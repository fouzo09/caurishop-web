@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Détails de l'Entreprise</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.companies.index') }}">Entreprises</a>
                <i class="fas fa-chevron-right"></i>
                <span>Détails</span>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert success"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
    @endif
    @if(session('error'))
    <div class="alert danger"><i class="fas fa-times-circle"></i><span>{{ session('error') }}</span></div>
    @endif

    {{-- Bannière d'action pour les demandes en attente --}}
    @if($company->isPending())
    <div style="background:#fffbeb;border:1.5px solid #f59e0b;border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.75rem;">
            <i class="fas fa-clock" style="color:#f59e0b;font-size:1.3rem;"></i>
            <div>
                <div style="font-weight:700;color:#92400e;">Demande en attente de validation</div>
                <div style="font-size:.875rem;color:#78350f;">Examinez les informations et les documents avant de valider ou rejeter cette demande.</div>
            </div>
        </div>
        <div style="display:flex;gap:.75rem;">
            <button type="button" class="btn btn-outline" style="color:var(--danger);border-color:var(--danger);" onclick="openRejectModal()">
                <i class="fas fa-times"></i> Rejeter
            </button>
            <button type="button" class="btn btn-primary" style="background:#16a34a;" onclick="openApproveModal()">
                <i class="fas fa-check"></i> Approuver
            </button>
        </div>
    </div>

    {{-- Modal rejet --}}
    <div id="rejectModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:90%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;">
            <div style="padding:1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <h3 style="margin:0;font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;">
                    <i class="fas fa-times-circle" style="color:var(--danger);"></i>
                    Rejeter la demande
                </h3>
                <button onclick="closeRejectModal()" style="background:none;border:none;font-size:1.4rem;color:var(--gray);cursor:pointer;line-height:1;">&times;</button>
            </div>
            <div style="padding:1.5rem;">
                <p style="color:var(--gray);font-size:.9rem;line-height:1.6;margin-bottom:.75rem;">
                    Vous allez rejeter la demande de <strong style="color:var(--dark);">{{ $company->raison_sociale }}</strong>.
                </p>
                <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.85rem;color:#991b1b;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Cette action est irréversible. L'entreprise ne pourra pas accéder à la plateforme.</span>
                </div>
            </div>
            <form action="{{ route('admin.companies.reject', $company) }}" method="POST">
                @csrf
                <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.75rem;justify-content:flex-end;">
                    <button type="button" onclick="closeRejectModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="background:var(--danger);">
                        <i class="fas fa-times"></i> Confirmer le rejet
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal approbation avec limite de crédit --}}
    <div id="approveModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:12px;width:90%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;">
            <div style="padding:1.5rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                <h3 style="margin:0;font-size:1.1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;">
                    <i class="fas fa-check-circle" style="color:#16a34a;"></i>
                    Approuver l'entreprise
                </h3>
                <button onclick="closeApproveModal()" style="background:none;border:none;font-size:1.4rem;color:var(--gray);cursor:pointer;line-height:1;">&times;</button>
            </div>
            <form action="{{ route('admin.companies.approve', $company) }}" method="POST">
                @csrf
                <div style="padding:1.5rem;">
                    <p style="margin-bottom:1.25rem;color:var(--gray);font-size:.9rem;line-height:1.6;">
                        Vous allez approuver et activer <strong style="color:var(--dark);">{{ $company->raison_sociale }}</strong>.<br>
                        Définissez la limite de crédit accordée à cette entreprise.
                    </p>
                    <div style="margin-bottom:1rem;">
                        <label style="display:block;font-size:.85rem;font-weight:600;color:var(--dark);margin-bottom:.5rem;">
                            Limite de crédit (GNF) <span style="color:var(--danger);">*</span>
                        </label>
                        <input
                            type="number"
                            name="credit_limit"
                            id="creditLimitInput"
                            min="0"
                            step="1000"
                            required
                            placeholder="Ex : 5 000 000"
                            style="width:100%;padding:.75rem 1rem;border:1.5px solid var(--border);border-radius:8px;font-size:1rem;font-family:inherit;outline:none;transition:border-color .15s;"
                            onfocus="this.style.borderColor='var(--primary)'"
                            onblur="this.style.borderColor='var(--border)'"
                        />
                        <div style="font-size:.75rem;color:var(--gray);margin-top:.4rem;">Montant maximum que l'entreprise peut dépenser à crédit.</div>
                    </div>
                </div>
                <div style="padding:1rem 1.5rem;border-top:1px solid var(--border);display:flex;gap:.75rem;justify-content:flex-end;">
                    <button type="button" onclick="closeApproveModal()" class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-primary" style="background:#16a34a;">
                        <i class="fas fa-check"></i> Confirmer l'approbation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openRejectModal() {
        document.getElementById('rejectModal').style.display = 'flex';
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) closeRejectModal();
    });

    function openApproveModal() {
        const m = document.getElementById('approveModal');
        m.style.display = 'flex';
        setTimeout(() => document.getElementById('creditLimitInput').focus(), 50);
    }
    function closeApproveModal() {
        document.getElementById('approveModal').style.display = 'none';
    }
    document.getElementById('approveModal').addEventListener('click', function(e) {
        if (e.target === this) closeApproveModal();
    });
    </script>
    @endif

    <div class="card">
        <div class="card-body">

            {{-- En-tête entreprise --}}
            <div style="display:flex;align-items:start;gap:1.5rem;margin-bottom:2rem;padding-bottom:2rem;border-bottom:1px solid var(--border);">
                <div style="width:80px;height:80px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;flex-shrink:0;">
                    <i class="fas fa-building"></i>
                </div>
                <div style="flex:1;">
                    <h2 style="margin-bottom:.5rem;">{{ $company->raison_sociale }}</h2>
                    @if($company->registration_number)
                    <p style="color:var(--gray);margin-bottom:.75rem;"><i class="fas fa-id-card" style="margin-right:.5rem;"></i>{{ $company->registration_number }}</p>
                    @endif
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        @if($company->status === 'pending')
                            <span class="badge warning">En attente</span>
                        @elseif($company->status === 'approved')
                            <span class="badge success">Approuvée</span>
                        @else
                            <span class="badge danger">Rejetée</span>
                        @endif
                        @if($company->is_active)
                            <span class="badge success">Active</span>
                        @else
                            <span class="badge danger">Inactive</span>
                        @endif
                        <span class="badge primary">{{ $company->customers_count }} client(s)</span>
                    </div>
                </div>
            </div>

            {{-- Grille principale --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-bottom:2rem;">

                {{-- Gérant --}}
                @if($company->gerant_nom)
                <div>
                    <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--primary);"><i class="fas fa-user-tie"></i> Gérant / Responsable légal</h3>
                    <div style="display:flex;flex-direction:column;gap:.75rem;">
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Nom complet</div>
                            <div style="font-weight:600;">{{ $company->gerant_prenom }} {{ $company->gerant_nom }}</div>
                        </div>
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Téléphone</div>
                            <div style="font-weight:600;">{{ $company->gerant_tel }}</div>
                        </div>
                        @if($company->gerant_piece)
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Pièce d'identité</div>
                            <div style="font-weight:600;">{{ $company->gerant_piece }}</div>
                        </div>
                        @endif
                        @if($company->gerant_adresse)
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Adresse personnelle</div>
                            <div style="font-weight:600;">{{ $company->gerant_adresse }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Informations entreprise --}}
                <div>
                    <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--primary);"><i class="fas fa-address-book"></i> Informations de l'Entreprise</h3>
                    <div style="display:flex;flex-direction:column;gap:.75rem;">
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Email</div>
                            <div style="font-weight:600;">{{ $company->email ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Téléphone</div>
                            <div style="font-weight:600;">{{ $company->phone ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Adresse</div>
                            <div style="font-weight:600;">{{ $company->address ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Localisation</div>
                            <div style="font-weight:600;">{{ $company->city }}{{ $company->country ? ', '.$company->country : '' }}</div>
                        </div>
                        @if($company->date_creation)
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Date de création</div>
                            <div style="font-weight:600;">{{ $company->date_creation->format('d/m/Y') }}</div>
                        </div>
                        @endif
                        @if($company->nombre_employes)
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Nombre d'employés</div>
                            <div style="font-weight:600;">{{ $company->nombre_employes }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Financier --}}
                <div>
                    <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--primary);"><i class="fas fa-wallet"></i> Informations Financières</h3>
                    <div style="display:flex;flex-direction:column;gap:.75rem;">
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Limite de crédit</div>
                            <div style="font-weight:800;font-size:1.5rem;color:var(--primary);">
                                {{ $company->credit_limit ? number_format($company->credit_limit, 0, ',', ' ').' GNF' : '—' }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size:.85rem;color:var(--gray);margin-bottom:.25rem;">Enregistré le</div>
                            <div style="font-weight:600;">{{ $company->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Documents --}}
            @php
                $docs = [
                    'doc_rccm'     => ['label' => 'RCCM',                       'icon' => 'fa-file-alt',     'required' => true],
                    'doc_nif'      => ['label' => 'NIF',                        'icon' => 'fa-receipt',      'required' => true],
                    'doc_statuts'  => ['label' => 'Statuts de la société',      'icon' => 'fa-file-contract','required' => true],
                    'doc_cni'      => ['label' => "Pièce d'identité gérant",    'icon' => 'fa-id-card',      'required' => true],
                    'doc_patente'  => ['label' => 'Patente commerciale',        'icon' => 'fa-briefcase',    'required' => false],
                    'doc_domicile' => ['label' => 'Attestation domiciliation',  'icon' => 'fa-home',         'required' => false],
                ];
                $hasAnyDoc = collect($docs)->keys()->some(fn($k) => $company->$k);
            @endphp

            @if($hasAnyDoc)
            <div style="padding-top:2rem;border-top:1px solid var(--border);margin-top:1rem;">
                <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--primary);"><i class="fas fa-folder-open"></i> Documents juridiques</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;">
                    @foreach($docs as $field => $meta)
                        @if($company->$field)
                        <a href="{{ \App\Support\Media::url($company->$field) }}" target="_blank"
                           style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border:1.5px solid var(--border);border-radius:8px;background:var(--light);text-decoration:none;color:var(--dark);transition:border-color .15s;"
                           onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'">
                            <i class="fas {{ $meta['icon'] }}" style="color:var(--primary);font-size:1.25rem;flex-shrink:0;"></i>
                            <div>
                                <div style="font-weight:600;font-size:.875rem;">{{ $meta['label'] }}</div>
                                <div style="font-size:.75rem;color:var(--gray);">Cliquer pour voir</div>
                            </div>
                            <i class="fas fa-external-link-alt" style="margin-left:auto;color:var(--gray);font-size:.75rem;"></i>
                        </a>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Clients --}}
            @if($company->customers_count > 0)
            <div style="padding-top:2rem;border-top:1px solid var(--border);margin-top:2rem;">
                <h3 style="font-size:1rem;font-weight:600;margin-bottom:1rem;color:var(--primary);"><i class="fas fa-users"></i> Clients ({{ $company->customers_count }})</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1rem;">
                    @foreach($company->customers as $customer)
                    <div style="padding:1rem;background:var(--light);border-radius:8px;border:1px solid var(--border);">
                        <div style="font-weight:600;margin-bottom:.25rem;">{{ $customer->name }}</div>
                        <div style="font-size:.85rem;color:var(--gray);">{{ $customer->email }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        <div class="card-footer" style="display:flex;gap:1rem;justify-content:flex-end;flex-wrap:wrap;">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Retour</a>
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Modifier</a>

            @if($company->is_active)
            <form action="{{ route('admin.companies.deactivate', $company) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline" style="color:var(--warning);" onclick="return confirm('Désactiver ?')"><i class="fas fa-ban"></i> Désactiver</button>
            </form>
            @else
            <form action="{{ route('admin.companies.activate', $company) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline" style="color:var(--success);"><i class="fas fa-check"></i> Activer</button>
            </form>
            @endif

            @if($company->customers_count === 0)
            <form action="{{ route('admin.companies.destroy', $company) }}" method="POST" style="display:inline;">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline" style="color:var(--danger);" onclick="return confirm('Supprimer définitivement ?')"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
