@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Clients</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Clients</span>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Clients</div>
                <div class="stat-value">{{ $customers->total() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Clients Actifs</div>
                <div class="stat-value">{{ $customers->where('is_active', true)->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-user"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Particuliers</div>
                <div class="stat-value">{{ $customers->where('type', 'individual')->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Entreprises</div>
                <div class="stat-value">{{ $customers->where('type', 'company')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                Nouveau Client
            </a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Entreprise</th>
                        <th>Contact</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td>#{{ $customer->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $customer->displayName() }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $customer->address }}</div>
                        </td>
                        <td>
                            @if($customer->type === 'individual')
                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                                <i class="fas fa-user"></i>
                                Particulier
                            </span>
                            @else
                            <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                                <i class="fas fa-building"></i>
                                Entreprise
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($customer->company)
                            <div style="font-weight: 600; font-size: 0.85rem;">{{ $customer->company->name }}</div>
                            @else
                            <span style="color: var(--gray); font-size: 0.85rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $customer->email }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $customer->phone }}</div>
                        </td>
                        <td>
                            @if($customer->is_active)
                            <span class="badge success">Actif</span>
                            @else
                            <span class="badge danger">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($customer->is_active)
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--warning);" onclick="openDeactivateModal({{ $customer->id }}, '{{ $customer->displayName() }}')">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @else
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--success);" onclick="openActivateModal({{ $customer->id }}, '{{ $customer->displayName() }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);" onclick="openDeleteModal({{ $customer->id }}, '{{ $customer->displayName() }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="deactivate-form-{{ $customer->id }}" action="{{ route('admin.customers.deactivate', $customer) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <form id="activate-form-{{ $customer->id }}" action="{{ route('admin.customers.activate', $customer) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <form id="delete-form-{{ $customer->id }}" action="{{ route('admin.customers.destroy', $customer) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucun client trouvé
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Désactiver -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-ban"></i> Désactiver le client</h3>
            <button class="close-btn" onclick="closeModal('deactivateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment désactiver <strong id="deactivateCustomerName"></strong> ?</p>
            <div class="info-box warning">
                <i class="fas fa-info-circle"></i>
                <span>Le client ne pourra plus passer de commandes.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('deactivateModal')">Annuler</button>
            <button class="btn btn-primary" style="background: var(--warning);" onclick="confirmDeactivate()">Désactiver</button>
        </div>
    </div>
</div>

<!-- Modal Activer -->
<div id="activateModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-check-circle"></i> Activer le client</h3>
            <button class="close-btn" onclick="closeModal('activateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment activer <strong id="activateCustomerName"></strong> ?</p>
            <div class="info-box success">
                <i class="fas fa-info-circle"></i>
                <span>Le client pourra à nouveau passer des commandes.</span>
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
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer le client</h3>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer <strong id="deleteCustomerName"></strong> ?</p>
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
    let currentCustomerId = null;

    function openDeactivateModal(customerId, customerName) {
        currentCustomerId = customerId;
        document.getElementById('deactivateCustomerName').textContent = customerName;
        document.getElementById('deactivateModal').classList.add('active');
    }

    function openActivateModal(customerId, customerName) {
        currentCustomerId = customerId;
        document.getElementById('activateCustomerName').textContent = customerName;
        document.getElementById('activateModal').classList.add('active');
    }

    function openDeleteModal(customerId, customerName) {
        currentCustomerId = customerId;
        document.getElementById('deleteCustomerName').textContent = customerName;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentCustomerId = null;
    }

    function confirmDeactivate() {
        if (currentCustomerId) {
            document.getElementById('deactivate-form-' + currentCustomerId).submit();
        }
    }

    function confirmActivate() {
        if (currentCustomerId) {
            document.getElementById('activate-form-' + currentCustomerId).submit();
        }
    }

    function confirmDelete() {
        if (currentCustomerId) {
            document.getElementById('delete-form-' + currentCustomerId).submit();
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            currentCustomerId = null;
        }
    }
</script>
@endsection
