@extends('tpl.admin')
@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Sécurité</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Sécurité</span>
            </div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-list-alt"></i></div>
            <div class="stat-info">
                <div class="stat-label">Logs (30 derniers jours)</div>
                <div class="stat-value">{{ $logs->total() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <div class="stat-label">Sessions actives</div>
                <div class="stat-value">{{ $activeSessions->count() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Échecs de connexion récents</div>
                <div class="stat-value">{{ $recentFailed->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Onglets --}}
    <div class="profile-tabs" style="margin-bottom: 1rem;">
        <button class="profile-tab {{ $activeTab === 'logs' ? 'active' : '' }}" onclick="switchTab('logs')">
            <i class="fas fa-list-alt"></i> Journal d'activité
        </button>
        <button class="profile-tab {{ $activeTab === 'sessions' ? 'active' : '' }}" onclick="switchTab('sessions')">
            <i class="fas fa-globe"></i> Sessions actives
        </button>
        <button class="profile-tab {{ $activeTab === 'failed' ? 'active' : '' }}" onclick="switchTab('failed')">
            <i class="fas fa-ban"></i> Connexions échouées
            @if($recentFailed->count() > 0)
            <span class="badge danger" style="font-size: 0.7rem; margin-left: 0.3rem;">{{ $recentFailed->count() }}</span>
            @endif
        </button>
    </div>

    {{-- PANEL: Journal d'activité --}}
    <div id="panel-logs" class="profile-panel {{ $activeTab !== 'logs' ? 'hidden' : '' }}">

        {{-- Filtres --}}
        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-body" style="padding: 1rem;">
                <form method="GET" action="{{ route('admin.security.index') }}"
                      style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="tab" value="logs">

                    <div style="flex: 1; min-width: 160px;">
                        <label class="form-label" style="font-size: 0.8rem;">Recherche</label>
                        <input type="text" name="search" class="form-input" placeholder="Description..."
                               value="{{ $filters['search'] ?? '' }}">
                    </div>

                    <div style="min-width: 150px;">
                        <label class="form-label" style="font-size: 0.8rem;">Action</label>
                        <select name="action" class="form-input">
                            <option value="">Toutes</option>
                            @foreach($availableActions as $action)
                            <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>
                                {{ $action }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 150px;">
                        <label class="form-label" style="font-size: 0.8rem;">Utilisateur</label>
                        <select name="user_id" class="form-input">
                            <option value="">Tous</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ ($filters['user_id'] ?? '') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="min-width: 130px;">
                        <label class="form-label" style="font-size: 0.8rem;">Du</label>
                        <input type="date" name="date_from" class="form-input" value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    <div style="min-width: 130px;">
                        <label class="form-label" style="font-size: 0.8rem;">Au</label>
                        <input type="date" name="date_to" class="form-input" value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.security.index') }}" class="btn btn-outline btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="padding: 0;">
                <div class="table-container" style="margin: 0; border-radius: 0;">
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Utilisateur</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP</th>
                            <th>Navigateur</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td style="white-space: nowrap; font-size: 0.82rem; color: var(--gray);">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                @if($log->user)
                                <div style="font-weight: 600; font-size: 0.88rem;">{{ $log->user->name }}</div>
                                <div style="font-size: 0.78rem; color: var(--gray);">{{ $log->user->email }}</div>
                                @else
                                <span style="color: var(--gray); font-size: 0.85rem;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $log->badgeColor() }}" style="font-size: 0.75rem; font-family: monospace;">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td style="font-size: 0.88rem; max-width: 320px;">{{ $log->description }}</td>
                            <td style="font-family: monospace; font-size: 0.82rem; color: var(--gray);">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td style="font-size: 0.78rem; color: var(--gray); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                title="{{ $log->user_agent }}">
                                {{ $log->user_agent ? \Illuminate\Support\Str::limit($log->user_agent, 40) : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2.5rem; color: var(--gray);">
                                <i class="fas fa-inbox" style="font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.75rem;"></i>
                                Aucun log
                            </td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($logs->hasPages())
            <div class="card-footer">{{ $logs->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>

    {{-- PANEL: Sessions actives --}}
    <div id="panel-sessions" class="profile-panel {{ $activeTab !== 'sessions' ? 'hidden' : '' }}">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sessions actives ({{ $activeSessions->count() }})</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-container" style="margin: 0; border-radius: 0;">
                    <table>
                        <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>IP</th>
                            <th>Navigateur</th>
                            <th>Dernière activité</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($activeSessions as $session)
                        <tr {{ $session->is_current ? 'style=background:rgba(0,102,255,0.04)' : '' }}>
                            <td>
                                <div style="font-weight: 600; font-size: 0.88rem;">{{ $session->name }}</div>
                                <div style="font-size: 0.78rem; color: var(--gray);">{{ $session->email }}</div>
                            </td>
                            <td style="font-family: monospace; font-size: 0.85rem;">{{ $session->ip_address ?? '—' }}</td>
                            <td style="font-size: 0.8rem; color: var(--gray); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                                title="{{ $session->user_agent }}">
                                {{ $session->user_agent ? \Illuminate\Support\Str::limit($session->user_agent, 50) : '—' }}
                            </td>
                            <td style="font-size: 0.85rem;">
                                {{ $session->last_active_at->diffForHumans() }}
                                <div style="font-size: 0.78rem; color: var(--gray);">{{ $session->last_active_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                @if($session->is_current)
                                <span class="badge success" style="font-size: 0.75rem;">Session actuelle</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray);">Aucune session active</td>
                        </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PANEL: Connexions échouées --}}
    <div id="panel-failed" class="profile-panel {{ $activeTab !== 'failed' ? 'hidden' : '' }}">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">10 dernières tentatives échouées</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                @forelse($recentFailed as $log)
                <div style="display: flex; align-items: center; gap: 1rem; padding: 0.85rem 1.5rem; border-bottom: 1px solid var(--border);">
                    <div style="width: 36px; height: 36px; background: rgba(239,68,68,0.1); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-ban" style="color: var(--danger); font-size: 0.85rem;"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-size: 0.88rem; font-weight: 600; color: var(--danger);">{{ $log->description }}</div>
                        <div style="font-size: 0.78rem; color: var(--gray); margin-top: 0.15rem;">
                            IP : {{ $log->ip_address ?? '—' }} &nbsp;·&nbsp; {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--gray);">{{ $log->created_at->diffForHumans() }}</div>
                </div>
                @empty
                <div style="padding: 2rem; text-align: center; color: var(--gray);">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem; color: var(--success); display: block; margin-bottom: 0.5rem;"></i>
                    Aucune tentative échouée récente
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    .profile-tabs { display: flex; gap: 0.25rem; background: var(--light); padding: 0.35rem; border-radius: 10px; border: 1px solid var(--border); width: fit-content; }
    .profile-tab { padding: 0.55rem 1.25rem; border: none; background: transparent; border-radius: 7px; font-size: 0.9rem; font-weight: 500; cursor: pointer; color: var(--gray); display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
    .profile-tab.active { background: var(--white); color: var(--primary); box-shadow: 0 1px 4px rgba(0,0,0,0.08); font-weight: 600; }
    .profile-tab:hover:not(.active) { color: var(--dark); }
    .profile-panel.hidden { display: none; }
</style>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.profile-panel').forEach(p => p.classList.add('hidden'));
        document.getElementById('panel-' + tab).classList.remove('hidden');
        document.querySelector('[onclick="switchTab(\'' + tab + '\')"]').classList.add('active');
    }
</script>
@endsection
