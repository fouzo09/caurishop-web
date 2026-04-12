<?php

namespace App\Repositories\Admin;

use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function paginate(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = Payment::with(['customer', 'order', 'creator']);

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('payment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('payment_date', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('payment_date')->paginate($perPage);
    }
}
