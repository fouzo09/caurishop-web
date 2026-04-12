<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'description', 'ip_address', 'user_agent', 'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Actions groupées pour les badges ──────────────────────────────
    public function isAuthAction(): bool
    {
        return in_array($this->action, ['login', 'logout', 'login_failed']);
    }

    public function isSensitiveAction(): bool
    {
        return in_array($this->action, [
            'user_created', 'user_deleted', 'user_suspended',
            'role_created', 'role_updated', 'role_deleted',
            'settings_updated',
        ]);
    }

    public function badgeColor(): string
    {
        return match ($this->action) {
            'login'                                  => 'success',
            'logout'                                 => 'primary',
            'login_failed'                           => 'danger',
            'user_deleted', 'role_deleted'           => 'danger',
            'user_suspended'                         => 'warning',
            'user_created', 'role_created'           => 'success',
            'user_updated', 'role_updated', 'settings_updated' => 'primary',
            default                                  => 'primary',
        };
    }
}
