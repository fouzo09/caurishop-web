@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Entreprises</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Entreprises</span>
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
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Entreprises</div>
                <div class="stat-value">{{ $companies->total() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Entreprises Actives</div>
                <div class="stat-value">{{ $companies->where('is_active', true)->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Entreprises Inactives</div>
                <div class="stat-value">{{ $companies->where('is_active', false)->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Clients</div>
                <div class="stat-value">{{ $companies->sum('customers_count') }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                Nouvelle Entreprise
            </a>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Entreprise</th>
                        <th>Contact</th>
                        <th>Localisation</th>
                        <th>Limite Crédit</th>
                        <th>Clients</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>#{{ $company->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $company->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $company->registration_number }}</div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $company->email }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $company->phone }}</div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ $company->city }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">{{ $company->country }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 600; color: var(--primary);">
                                {{ number_format($company->credit_limit, 0, ',', ' ') }} GNF
                            </span>
                        </td>
                        <td>
                            <span class="badge primary">{{ $company->customers_count }}</span>
                        </td>
                        <td>
                            @if($company->is_active)
                            <span class="badge success">Active</span>
                            @else
                            <span class="badge danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($company->is_active)
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--warning);" onclick="openDeactivateModal({{ $company->id }}, '{{ $company->name }}')">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @else
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--success);" onclick="openActivateModal({{ $company->id }}, '{{ $company->name }}')">
                                    <i class="fas fa-check"></i>
                                </button>
                                @endif
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);" onclick="openDeleteModal({{ $company->id }}, '{{ $company->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="deactivate-form-{{ $company->id }}" action="{{ route('admin.companies.deactivate', $company) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <form id="activate-form-{{ $company->id }}" action="{{ route('admin.companies.activate', $company) }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <form id="delete-form-{{ $company->id }}" action="{{ route('admin.companies.destroy', $company) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucune entreprise trouvée
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
            <h3><i class="fas fa-ban"></i> Désactiver l'entreprise</h3>
            <button class="close-btn" onclick="closeModal('deactivateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment désactiver <strong id="deactivateCompanyName"></strong> ?</p>
            <div class="info-box warning">
                <i class="fas fa-info-circle"></i>
                <span>Les clients de cette entreprise ne pourront plus passer de commandes.</span>
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
            <h3><i class="fas fa-check-circle"></i> Activer l'entreprise</h3>
            <button class="close-btn" onclick="closeModal('activateModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment activer <strong id="activateCompanyName"></strong> ?</p>
            <div class="info-box success">
                <i class="fas fa-info-circle"></i>
                <span>Les clients pourront à nouveau passer des commandes.</span>
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
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer l'entreprise</h3>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer <strong id="deleteCompanyName"></strong> ?</p>
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
    let currentCompanyId = null;

    function openDeactivateModal(companyId, companyName) {
        currentCompanyId = companyId;
        document.getElementById('deactivateCompanyName').textContent = companyName;
        document.getElementById('deactivateModal').classList.add('active');
    }

    function openActivateModal(companyId, companyName) {
        currentCompanyId = companyId;
        document.getElementById('activateCompanyName').textContent = companyName;
        document.getElementById('activateModal').classList.add('active');
    }

    function openDeleteModal(companyId, companyName) {
        currentCompanyId = companyId;
        document.getElementById('deleteCompanyName').textContent = companyName;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentCompanyId = null;
    }

    function confirmDeactivate() {
        if (currentCompanyId) {
            document.getElementById('deactivate-form-' + currentCompanyId).submit();
        }
    }

    function confirmActivate() {
        if (currentCompanyId) {
            document.getElementById('activate-form-' + currentCompanyId).submit();
        }
    }

    function confirmDelete() {
        if (currentCompanyId) {
            document.getElementById('delete-form-' + currentCompanyId).submit();
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            currentCompanyId = null;
        }
    }
</script>
@endsection
