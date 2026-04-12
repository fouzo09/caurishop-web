@extends('tpl.admin')
@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Notifications</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Notifications</span>
            </div>
        </div>
        @if($unreadCount > 0)
        <form action="{{ route('admin.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">
                <i class="fas fa-check-double"></i>
                Tout marquer comme lu
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="alert success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Toutes les notifications
                @if($unreadCount > 0)
                <span class="badge danger" style="font-size: 0.75rem; margin-left: 0.4rem;">{{ $unreadCount }} non lue(s)</span>
                @endif
            </h3>
        </div>
        <div style="divide-color: var(--border);">
            @forelse($notifications as $notif)
            <div class="notif-row {{ $notif->isRead() ? 'read' : 'unread' }}">
                <div class="notif-icon {{ $notif->color }}">
                    <i class="fas {{ $notif->icon }}"></i>
                </div>
                <div class="notif-content">
                    <div class="notif-title">
                        {{ $notif->title }}
                        @if(!$notif->isRead())
                        <span style="display: inline-block; width: 7px; height: 7px; background: var(--primary); border-radius: 50%; margin-left: 0.4rem; vertical-align: middle;"></span>
                        @endif
                    </div>
                    <div class="notif-message">{{ $notif->message }}</div>
                    <div class="notif-meta">{{ $notif->created_at->diffForHumans() }}</div>
                </div>
                <div class="notif-actions">
                    @if($notif->link)
                    <a href="{{ $notif->link }}" class="btn btn-outline btn-sm" title="Voir">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                    @endif
                    @if(!$notif->isRead())
                    <form action="{{ route('admin.notifications.read', $notif->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" title="Marquer comme lu">
                            <i class="fas fa-check"></i>
                        </button>
                    </form>
                    @endif
                    <form action="{{ route('admin.notifications.destroy', $notif->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding: 3rem; text-align: center; color: var(--gray);">
                <i class="fas fa-bell-slash" style="font-size: 2.5rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                Aucune notification
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

<style>
    .notif-row {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }

    .notif-row:last-child { border-bottom: none; }

    .notif-row.unread { background: rgba(0, 102, 255, 0.03); }
    .notif-row:hover { background: var(--light); }

    .notif-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.95rem;
    }

    .notif-icon.primary { background: rgba(0,102,255,0.1); color: var(--primary); }
    .notif-icon.success { background: rgba(16,185,129,0.1); color: var(--success); }
    .notif-icon.warning { background: rgba(245,158,11,0.1); color: var(--warning); }
    .notif-icon.danger  { background: rgba(239,68,68,0.1);  color: var(--danger); }

    .notif-content { flex: 1; min-width: 0; }
    .notif-title   { font-weight: 600; font-size: 0.9rem; margin-bottom: 0.2rem; }
    .notif-message { font-size: 0.85rem; color: var(--gray); line-height: 1.5; }
    .notif-meta    { font-size: 0.78rem; color: var(--gray); margin-top: 0.3rem; }
    .notif-actions { display: flex; gap: 0.4rem; flex-shrink: 0; }
</style>
@endsection
