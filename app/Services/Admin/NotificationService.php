<?php

namespace App\Services\Admin;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class NotificationService
{
    /**
     * Crée une notification pour tous les admins (ou un utilisateur précis).
     */
    public function notify(
        string $type,
        string $title,
        string $message,
        string $icon = 'fa-bell',
        string $color = 'primary',
        ?string $link = null,
        ?int $userId = null,
    ): void {
        if ($userId) {
            AppNotification::create(compact('user_id', 'type', 'title', 'message', 'icon', 'color', 'link') + ['user_id' => $userId]);
            return;
        }

        // Notifie tous les admins actifs
        $adminIds = User::role('admin')->where('is_active', true)->pluck('id');

        $rows = $adminIds->map(fn ($id) => [
            'user_id'    => $id,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'icon'       => $icon,
            'color'      => $color,
            'link'       => $link,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        if (!empty($rows)) {
            AppNotification::insert($rows);
        }
    }

    public function forUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return AppNotification::forUser($userId)
            ->latest()
            ->paginate($perPage);
    }

    public function unreadCount(int $userId): int
    {
        return AppNotification::forUser($userId)->unread()->count();
    }

    public function recentUnread(int $userId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return AppNotification::forUser($userId)
            ->unread()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function markRead(int $notificationId, int $userId): void
    {
        AppNotification::forUser($userId)
            ->where('id', $notificationId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function markAllRead(int $userId): void
    {
        AppNotification::forUser($userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function delete(int $notificationId, int $userId): void
    {
        AppNotification::forUser($userId)->where('id', $notificationId)->delete();
    }

    // ── Helpers sémantiques ──────────────────────────────────────────

    public function newOrder(string $orderNumber, string $customerName, string $link): void
    {
        $this->notify(
            type:    'order_created',
            title:   'Nouvelle commande',
            message: "La commande {$orderNumber} de {$customerName} vient d'être créée.",
            icon:    'fa-shopping-cart',
            color:   'primary',
            link:    $link,
        );
    }

    public function paymentReceived(string $amount, string $customerName, string $link): void
    {
        $this->notify(
            type:    'payment_received',
            title:   'Paiement reçu',
            message: "Paiement de {$amount} GNF reçu de {$customerName}.",
            icon:    'fa-coins',
            color:   'success',
            link:    $link,
        );
    }

    public function installmentLate(string $customerName, string $dueDate, string $amount, string $link): void
    {
        $this->notify(
            type:    'installment_late',
            title:   'Échéance en retard',
            message: "Échéance de {$amount} GNF de {$customerName} due le {$dueDate} est en retard.",
            icon:    'fa-exclamation-triangle',
            color:   'danger',
            link:    $link,
        );
    }

    public function newCompanyRegistration(string $companyName, string $link): void
    {
        $this->notify(
            type:    'company_registration',
            title:   'Nouvelle demande d\'inscription',
            message: "{$companyName} a soumis une demande d'inscription et attend validation.",
            icon:    'fa-building',
            color:   'warning',
            link:    $link,
        );
    }
}
