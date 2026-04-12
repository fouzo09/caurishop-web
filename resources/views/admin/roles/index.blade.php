@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Rôles</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Rôles</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert danger">
        <i class="fas fa-times-circle"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-user-tag"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Rôles</div>
                <div class="stat-value">{{ $roles->total() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                Nouveau Rôle
            </a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Permissions</th>
                        <th>Utilisateurs</th>
                        <th>Créé le</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($roles as $role)
                    <tr>
                        <td>#{{ $role->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ ucfirst($role->name) }}</div>
                            @if(in_array($role->name, ['admin', 'employee']))
                            <span class="badge warning" style="font-size: 0.7rem;">Système</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge primary">{{ $role->permissions->count() }} permission(s)</span>
                        </td>
                        <td>
                            <span class="badge" style="background: var(--light); color: var(--dark);">
                                {{ $role->users_count }} utilisateur(s)
                            </span>
                        </td>
                        <td>{{ $role->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(!in_array($role->name, ['admin', 'employee']))
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);"
                                        onclick="openDeleteModal({{ $role->id }}, '{{ $role->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucun rôle trouvé
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($roles->hasPages())
            <div style="margin-top: 1.5rem;">
                {{ $roles->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Supprimer -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer le rôle</h3>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer le rôle <strong id="deleteRoleName"></strong> ?</p>
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
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-dialog { background: var(--white); border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
    .close-btn { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px; }
    .close-btn:hover { background: var(--light); color: var(--dark); }
    .modal-body { padding: 1.5rem; }
    .modal-body p { margin-bottom: 1rem; line-height: 1.6; }
    .info-box { padding: 0.75rem 1rem; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
    .info-box.danger { background: rgba(239,68,68,0.1); color: var(--danger); border-left: 3px solid var(--danger); }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 0.75rem; justify-content: flex-end; }
</style>

<script>
    let currentRoleId = null;

    function openDeleteModal(roleId, roleName) {
        currentRoleId = roleId;
        document.getElementById('deleteRoleName').textContent = roleName;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentRoleId = null;
    }

    function confirmDelete() {
        if (currentRoleId) {
            document.getElementById('delete-form-' + currentRoleId).submit();
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            currentRoleId = null;
        }
    }
</script>
@endsection
