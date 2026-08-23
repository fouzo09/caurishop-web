@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des utilisateurs</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Liste des utilisateurs</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Utilisateurs</div>
                <div class="stat-value">{{ $users->total() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Utilisateurs Actifs</div>
                <div class="stat-value">{{ $users->where('is_active', true)->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-user-slash"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Utilisateurs Suspendus</div>
                <div class="stat-value">{{ $users->where('is_active', false)->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-user-tag"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Rôles</div>
                <div class="stat-value">{{ $roles->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                Nouvel Utilisateur
            </a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Rôles</th>
                        <th>Email Vérifié</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>#{{ $user->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $user->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $user->email }}</div>
                        </td>
                        <td>
                            @forelse($user->roles as $role)
                            <span class="badge primary">{{ ucfirst($role->name) }}</span>
                            @empty
                            <span class="badge" style="background: var(--light); color: var(--gray);">Aucun rôle</span>
                            @endforelse
                        </td>
                        <td>
                            @if($user->email_verified_at)
                            <span class="badge success">Vérifié</span>
                            @else
                            <span class="badge warning">Non vérifié</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                            <span class="badge success">Actif</span>
                            @else
                            <span class="badge danger">Suspendu</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->is_active)
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--warning);" onclick="openSuspendModal({{ $user->id }}, '{{ $user->name }}')">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @else
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--success);" onclick="openActivateModal({{ $user->id }}, '{{ $user->name }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);" onclick="openDeleteModal({{ $user->id }}, '{{ $user->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="suspend-form-{{ $user->id }}" action="{{ route('admin.users.suspend', $user) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <form id="activate-form-{{ $user->id }}" action="{{ route('admin.users.activate', $user) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucun utilisateur trouvé
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Suspendre -->
<div id="suspendModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-ban"></i> Suspendre l'utilisateur</h3>
            <button class="close-btn" onclick="closeModal('suspendModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment suspendre <strong id="suspendUserName"></strong> ?</p>
            <div class="info-box warning">
                <i class="fas fa-info-circle"></i>
                <span>L'utilisateur ne pourra plus se connecter.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('suspendModal')">Annuler</button>
            <button class="btn btn-primary" style="background: var(--warning);" onclick="confirmSuspend()">Suspendre</button>
        </div>
    </div>
</div>

<!-- Modal Activer -->
<div id="activateModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-check-circle"></i> Activer l'utilisateur</h3>
            <button class="close-btn" onclick="closeModal('activateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment activer <strong id="activateUserName"></strong> ?</p>
            <div class="info-box success">
                <i class="fas fa-info-circle"></i>
                <span>L'utilisateur pourra à nouveau se connecter.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('activateModal')">Annuler</button>
            <button class="btn btn-primary" style="background: var(--success);" onclick="confirmActivate()">Activer</button>
        </div>
    </div>
</div>

<!-- Modal Supprimer -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer l'utilisateur</h3>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer <strong id="deleteUserName"></strong> ?</p>
            <div class="info-box danger">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Cette action est irréversible.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('deleteModal')">Annuler</button>
            <button class="btn btn-primary" style="background: var(--danger);" onclick="confirmDelete()">Supprimer</button>
        </div>
    </div>
</div>

<style>
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-dialog {
        background: var(--white);
        border-radius: 8px;
        width: 90%;
        max-width: 450px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--gray);
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    .close-btn:hover {
        background: var(--light);
        color: var(--dark);
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-body p {
        margin-bottom: 1rem;
        line-height: 1.6;
    }

    .info-box {
        padding: 0.75rem 1rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.9rem;
    }

    .info-box.warning {
        background: rgba(245, 158, 11, 0.1);
        color: var(--warning);
        border-left: 3px solid var(--warning);
    }

    .info-box.success {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
        border-left: 3px solid var(--success);
    }

    .info-box.danger {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
        border-left: 3px solid var(--danger);
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        gap: 0.75rem;
        justify-content: flex-end;
    }
</style>

<script>
    let currentUserId = null;

    function openSuspendModal(userId, userName) {
        currentUserId = userId;
        document.getElementById('suspendUserName').textContent = userName;
        document.getElementById('suspendModal').classList.add('active');
    }

    function openActivateModal(userId, userName) {
        currentUserId = userId;
        document.getElementById('activateUserName').textContent = userName;
        document.getElementById('activateModal').classList.add('active');
    }

    function openDeleteModal(userId, userName) {
        currentUserId = userId;
        document.getElementById('deleteUserName').textContent = userName;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentUserId = null;
    }

    function confirmSuspend() {
        if (currentUserId) {
            document.getElementById('suspend-form-' + currentUserId).submit();
        }
    }

    function confirmActivate() {
        if (currentUserId) {
            document.getElementById('activate-form-' + currentUserId).submit();
        }
    }

    function confirmDelete() {
        if (currentUserId) {
            document.getElementById('delete-form-' + currentUserId).submit();
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            currentUserId = null;
        }
    }
</script>
@endsection
