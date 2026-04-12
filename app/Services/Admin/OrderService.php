<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CreditPlan;
use App\Models\Installment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Admin\OrderRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected OrderRepository $orderRepository
    ) {}

    public function getPaginatedOrders(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->paginate($perPage, $filters);
    }

    public function getOrderById(string $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    public function createOrder(array $data): Order
    {
        DB::beginTransaction();

        try {
            // Calcul du total
            $totalAmount = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

                $unitPrice = $variant ? (float) $variant->price : (float) $product->price;
                $quantity  = (int) $item['quantity'];
                $lineTotal = $unitPrice * $quantity;

                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity'   => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                ];
            }

            // Vérification du plafond crédit
            if ($data['order_type'] === Order::TYPE_CREDIT) {
                $customer     = \App\Models\Customer::findOrFail($data['customer_id']);
                $downPayment  = (float) ($data['down_payment'] ?? 0);
                $creditAmount = $totalAmount - $downPayment;

                $available = $customer->availableCredit();
                if (!is_null($available) && $creditAmount > $available) {
                    throw new \Exception(
                        "Plafond crédit dépassé. Disponible : " . number_format($available, 0, ',', ' ') . " GNF, demandé : " . number_format($creditAmount, 0, ',', ' ') . " GNF."
                    );
                }
            }

            // Création de la commande
            $order = $this->orderRepository->create([
                'order_number'              => $this->generateOrderNumber(),
                'customer_id'               => $data['customer_id'],
                'order_type'                => $data['order_type'],
                'status'                    => Order::STATUS_DRAFT,
                'total_amount'              => $totalAmount,
                'down_payment'              => $data['down_payment'] ?? 0,
                'credit_installments_count' => $data['credit_installments_count'] ?? null,
                'created_by'                => auth()->id(),
            ]);

            // Création des lignes de commande
            foreach ($itemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            DB::commit();

            return $order->fresh(['items.product', 'items.variant', 'customer']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirmOrder(Order $order): Order
    {
        if ($order->status !== Order::STATUS_DRAFT) {
            throw new \Exception('Seules les commandes en brouillon peuvent être confirmées.');
        }

        DB::beginTransaction();

        try {
            $order->update([
                'status'       => Order::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            if ($order->isCredit()) {
                $this->generateCreditPlan($order);
            }

            DB::commit();

            return $order->fresh(['items', 'creditPlan.installments']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deliverOrder(Order $order): Order
    {
        if ($order->status !== Order::STATUS_CONFIRMED) {
            throw new \Exception('Seules les commandes confirmées peuvent être livrées.');
        }

        DB::beginTransaction();

        try {
            $order->update([
                'status'       => Order::STATUS_COMPLETED,
                'delivered_at' => now(),
            ]);

            // Décrémentation du stock à la livraison
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    ProductVariant::where('id', $item->variant_id)
                        ->decrement('stock_quantity', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)
                        ->decrement('stock_quantity', $item->quantity);
                }
            }

            DB::commit();

            return $order->fresh(['items']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function cancelOrder(Order $order): Order
    {
        if ($order->status === Order::STATUS_COMPLETED) {
            throw new \Exception('Une commande livrée ne peut pas être annulée.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            throw new \Exception('La commande est déjà annulée.');
        }

        DB::beginTransaction();

        try {
            $order->update(['status' => Order::STATUS_CANCELLED]);

            // Clôturer le plan de crédit si existant
            if ($order->creditPlan) {
                $order->creditPlan->update(['status' => CreditPlan::STATUS_CLOSED]);
            }

            DB::commit();

            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateCreditPlan(Order $order): CreditPlan
    {
        $downPayment       = (float) $order->down_payment;
        $creditAmount      = (float) $order->total_amount - $downPayment;
        $installmentsCount = (int) $order->credit_installments_count;

        $plan = CreditPlan::create([
            'order_id'            => $order->id,
            'duration_months'     => $installmentsCount,
            'installments_count'  => $installmentsCount,
            'total_amount'        => $creditAmount,
            'down_payment_amount' => $downPayment,
            'outstanding_amount'  => $creditAmount,
            'status'              => CreditPlan::STATUS_ACTIVE,
        ]);

        $monthlyAmount = round($creditAmount / $installmentsCount, 2);
        $confirmedAt   = $order->confirmed_at;

        for ($i = 1; $i <= $installmentsCount; $i++) {
            Installment::create([
                'credit_plan_id'     => $plan->id,
                'installment_number' => $i,
                'due_date'           => $confirmedAt->copy()->addMonths($i),
                'amount_due'         => $monthlyAmount,
                'amount_paid'        => 0,
                'status'             => Installment::STATUS_PENDING,
            ]);
        }

        return $plan;
    }

    private function generateOrderNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = "CS-{$date}-";
        $last   = Order::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
