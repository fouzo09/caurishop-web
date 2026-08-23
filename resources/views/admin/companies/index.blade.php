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

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-building"></i></div>
            <div class="stat-info">
                <div class="stat-label">Total Entreprises</div>
                <div class="stat-value">{{ $companies->total() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
            <div class="stat-info">
                <div class="stat-label">Demandes en attente</div>
                <div class="stat-value">{{ $pendingCount }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Entreprises Actives</div>
                <div class="stat-value">{{ $companies->where('is_active', true)->count() }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon red"><i class="fas fa-times-circle"></i></div>
            <div class="stat-info">
                <div class="stat-label">Inactives</div>
                <div class="stat-value">{{ $companies->where('is_active', false)->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <a href="{{ route('admin.companies.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline' }}">Toutes</a>
                <a href="{{ route('admin.companies.index', ['status'=>'pending']) }}" class="btn btn-sm {{ request('status')==='pending' ? 'btn-primary' : 'btn-outline' }}" style="{{ request('status')==='pending' ? '' : 'color:var(--warning)' }}">
                    <i class="fas fa-clock"></i> En attente @if($pendingCount > 0)<span style="background:var(--warning);color:#fff;border-radius:9999px;padding:1px 7px;font-size:.75rem;margin-left:4px;">{{ $pendingCount }}</span>@endif
                </a>
                <a href="{{ route('admin.companies.index', ['status'=>'approved']) }}" class="btn btn-sm {{ request('status')==='approved' ? 'btn-primary' : 'btn-outline' }}">Approuvées</a>
                <a href="{{ route('admin.companies.index', ['status'=>'rejected']) }}" class="btn btn-sm {{ request('status')==='rejected' ? 'btn-primary' : 'btn-outline' }}">Rejetées</a>
            </div>
            <a href="{{ route('admin.companies.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Nouvelle Entreprise
            </a>
        </div>

        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>Raison sociale</th>
                        <th>Contact</th>
                        <th>Localisation</th>
                        @if($showImportance)
                        <th>Employés</th>
                        <th>Score</th>
                        @else
                        <th>Limite Crédit</th>
                        <th>Clients</th>
                        @endif
                        <th>Demande</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($companies as $company)
                    @php $score = $scores->get($company->id, 0); @endphp
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $company->raison_sociale }}</div>
                            <div style="font-size:.8rem;color:var(--gray);">{{ $company->registration_number }}</div>
                        </td>
                        <td>
                            <div style="font-size:.85rem;">{{ $company->email }}</div>
                            <div style="font-size:.8rem;color:var(--gray);">{{ $company->phone }}</div>
                        </td>
                        <td>
                            <div style="font-size:.85rem;">{{ $company->city }}</div>
                            <div style="font-size:.8rem;color:var(--gray);">{{ $company->country }}</div>
                        </td>
                        @if($showImportance)
                        <td>
                            <span style="font-size:.85rem;font-weight:600;">{{ $company->nombre_employes ?? '—' }}</span>
                        </td>
                        <td>
                            @php
                                $color = match(true) {
                                    $score >= 8 => ['bg'=>'#dcfce7','text'=>'#166534','label'=>'Critique'],
                                    $score >= 6 => ['bg'=>'#fef9c3','text'=>'#713f12','label'=>'Haute'],
                                    $score >= 4 => ['bg'=>'#ffedd5','text'=>'#9a3412','label'=>'Moyenne'],
                                    $score >= 2 => ['bg'=>'#f3f4f6','text'=>'#374151','label'=>'Faible'],
                                    default     => ['bg'=>'#f9fafb','text'=>'#9ca3af','label'=>'Min'],
                                };
                            @endphp
                            <div style="display:flex;flex-direction:column;align-items:flex-start;gap:3px;">
                                <span style="font-weight:900;font-size:1rem;color:{{ $color['text'] }};">{{ $score }}<span style="font-size:.7rem;font-weight:500;">/10</span></span>
                                <span style="font-size:.65rem;font-weight:700;padding:1px 7px;border-radius:4px;background:{{ $color['bg'] }};color:{{ $color['text'] }};">{{ $color['label'] }}</span>
                            </div>
                        </td>
                        @else
                        <td>
                            @if($company->credit_limit)
                            <span style="font-weight:600;color:var(--primary);">{{ number_format($company->credit_limit, 0, ',', ' ') }} GNF</span>
                            @else
                            <span style="color:var(--gray);">—</span>
                            @endif
                        </td>
                        <td><span class="badge primary">{{ $company->customers_count }}</span></td>
                        @endif
                        <td>
                            @if($company->status === 'pending')
                                <span class="badge warning">En attente</span>
                            @elseif($company->status === 'approved')
                                <span class="badge success">Approuvée</span>
                            @else
                                <span class="badge danger">Rejetée</span>
                            @endif
                        </td>
                        <td>
                            @if($company->is_active)
                                <span class="badge success">Active</span>
                            @else
                                <span class="badge danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.5rem;">
                                <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-outline btn-sm"><i class="fas fa-edit"></i></a>
                                @if($company->is_active)
                                <button type="button" class="btn btn-outline btn-sm" style="color:var(--warning);" onclick="openDeactivateModal({{ $company->id }}, '{{ $company->raison_sociale }}')"><i class="fas fa-ban"></i></button>
                                @else
                                <button type="button" class="btn btn-outline btn-sm" style="color:var(--success);" onclick="openActivateModal({{ $company->id }}, '{{ $company->raison_sociale }}')"><i class="fas fa-check"></i></button>
                                @endif
                                <button type="button" class="btn btn-outline btn-sm" style="color:var(--danger);" onclick="openDeleteModal({{ $company->id }}, '{{ $company->raison_sociale }}')"><i class="fas fa-trash"></i></button>
                            </div>
                            <form id="deactivate-form-{{ $company->id }}" action="{{ route('admin.companies.deactivate', $company) }}" method="POST" style="display:none;">@csrf</form>
                            <form id="activate-form-{{ $company->id }}" action="{{ route('admin.companies.activate', $company) }}" method="POST" style="display:none;">@csrf</form>
                            <form id="delete-form-{{ $company->id }}" action="{{ route('admin.companies.destroy', $company) }}" method="POST" style="display:none;">@csrf @method('DELETE')</form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center;padding:3rem;color:var(--gray);">
                            <i class="fas fa-inbox" style="font-size:3rem;opacity:.3;display:block;margin-bottom:1rem;"></i>
                            Aucune entreprise trouvée
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($companies->hasPages())
            <div style="margin-top:1rem;">{{ $companies->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modals -->
<div id="deactivateModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header"><h3><i class="fas fa-ban"></i> Désactiver l'entreprise</h3><button class="close-btn" onclick="closeModal('deactivateModal')">&times;</button></div>
        <div class="modal-body"><p>Voulez-vous désactiver <strong id="deactivateCompanyName"></strong> ?</p><div class="info-box warning"><i class="fas fa-info-circle"></i><span>Les clients ne pourront plus passer de commandes.</span></div></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('deactivateModal')">Annuler</button><button class="btn btn-primary" style="background:var(--warning);" onclick="confirmDeactivate()">Désactiver</button></div>
    </div>
</div>
<div id="activateModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header"><h3><i class="fas fa-check-circle"></i> Activer l'entreprise</h3><button class="close-btn" onclick="closeModal('activateModal')">&times;</button></div>
        <div class="modal-body"><p>Activer <strong id="activateCompanyName"></strong> ?</p><div class="info-box success"><i class="fas fa-info-circle"></i><span>Les clients pourront à nouveau commander.</span></div></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('activateModal')">Annuler</button><button class="btn btn-primary" style="background:var(--success);" onclick="confirmActivate()">Activer</button></div>
    </div>
</div>
<div id="deleteModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header"><h3><i class="fas fa-exclamation-triangle"></i> Supprimer l'entreprise</h3><button class="close-btn" onclick="closeModal('deleteModal')">&times;</button></div>
        <div class="modal-body"><p>Supprimer définitivement <strong id="deleteCompanyName"></strong> ?</p><div class="info-box danger"><i class="fas fa-exclamation-triangle"></i><span>Cette action est irréversible.</span></div></div>
        <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('deleteModal')">Annuler</button><button class="btn btn-primary" style="background:var(--danger);" onclick="confirmDelete()">Supprimer</button></div>
    </div>
</div>

<style>
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center}
.modal-overlay.active{display:flex}
.modal-dialog{background:var(--white);border-radius:8px;width:90%;max-width:450px;box-shadow:0 10px 40px rgba(0,0,0,.2)}
.modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.modal-header h3{margin:0;font-size:1.1rem;font-weight:600;display:flex;align-items:center;gap:.5rem}
.close-btn{background:none;border:none;font-size:1.5rem;color:var(--gray);cursor:pointer;padding:0;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:4px}
.close-btn:hover{background:var(--light);color:var(--dark)}
.modal-body{padding:1.5rem}
.modal-body p{margin-bottom:1rem;line-height:1.6}
.info-box{padding:.75rem 1rem;border-radius:6px;display:flex;align-items:center;gap:.75rem;font-size:.9rem}
.info-box.warning{background:rgba(245,158,11,.1);color:var(--warning);border-left:3px solid var(--warning)}
.info-box.success{background:rgba(16,185,129,.1);color:var(--success);border-left:3px solid var(--success)}
.info-box.danger{background:rgba(239,68,68,.1);color:var(--danger);border-left:3px solid var(--danger)}
.modal-footer{padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.75rem;justify-content:flex-end}
</style>

<script>
let currentCompanyId = null;
function openDeactivateModal(id, name){ currentCompanyId=id; document.getElementById('deactivateCompanyName').textContent=name; document.getElementById('deactivateModal').classList.add('active'); }
function openActivateModal(id, name){ currentCompanyId=id; document.getElementById('activateCompanyName').textContent=name; document.getElementById('activateModal').classList.add('active'); }
function openDeleteModal(id, name){ currentCompanyId=id; document.getElementById('deleteCompanyName').textContent=name; document.getElementById('deleteModal').classList.add('active'); }
function closeModal(id){ document.getElementById(id).classList.remove('active'); currentCompanyId=null; }
function confirmDeactivate(){ if(currentCompanyId) document.getElementById('deactivate-form-'+currentCompanyId).submit(); }
function confirmActivate(){ if(currentCompanyId) document.getElementById('activate-form-'+currentCompanyId).submit(); }
function confirmDelete(){ if(currentCompanyId) document.getElementById('delete-form-'+currentCompanyId).submit(); }
window.onclick = e => { if(e.target.classList.contains('modal-overlay')){ e.target.classList.remove('active'); currentCompanyId=null; } }
</script>
@endsection
