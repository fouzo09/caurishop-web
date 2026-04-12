<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogService
{
    public function log(
        string $action,
        string $description,
        ?int $userId = null,
        array $metadata = [],
    ): void {
        $request = request();

        ActivityLog::create([
            'user_id'    => $userId ?? auth()->id(),
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata'   => empty($metadata) ? null : $metadata,
        ]);
    }

    public function paginate(int $perPage = 30, array $filters = []): LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('description', 'ilike', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    public function getActiveSessions(): \Illuminate\Support\Collection
    {
        return \DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id as session_id',
                'users.id as user_id',
                'users.name',
                'users.email',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
            )
            ->whereNotNull('sessions.user_id')
            ->orderByDesc('sessions.last_activity')
            ->get()
            ->map(function ($s) {
                $s->last_active_at = \Carbon\Carbon::createFromTimestamp($s->last_activity);
                $s->is_current = $s->session_id === session()->getId();
                return $s;
            });
    }

    public function getRecentFailedLogins(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return ActivityLog::where('action', 'login_failed')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function availableActions(): array
    {
        return ActivityLog::distinct()->pluck('action')->sort()->values()->toArray();
    }
}
