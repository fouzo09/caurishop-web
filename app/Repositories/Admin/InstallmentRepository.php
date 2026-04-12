<?php

namespace App\Repositories\Admin;

use App\Models\Installment;
use Illuminate\Pagination\LengthAwarePaginator;

class InstallmentRepository
{
    public function markLateInstallments(): void
    {
        Installment::where('due_date', '<', today())
            ->whereIn('status', [Installment::STATUS_PENDING, Installment::STATUS_PARTIAL])
            ->update(['status' => Installment::STATUS_LATE]);
    }

    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = Installment::with(['creditPlan.order.customer.company']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['customer_id'])) {
            $query->whereHas('creditPlan.order', fn($q) =>
                $q->where('customer_id', $filters['customer_id'])
            );
        }

        if (!empty($filters['date_from'])) {
            $query->where('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('due_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('due_date')->paginate($perPage);
    }

    public function countByStatus(): array
    {
        return Installment::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
    }
}
