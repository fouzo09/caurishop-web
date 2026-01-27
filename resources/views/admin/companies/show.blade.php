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

    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: start; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
                <div style="width: 80px; height: 80px; background: var(--primary); color: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 2rem;">
                    <i class="fas fa-building"></i>
                </div>
                <div style="flex: 1;">
                    <h2 style="margin-bottom: 0.5rem;">{{ $company->name }}</h2>
                    <p style="color: var(--gray); margin-bottom: 0.75rem;">
                        <i class="fas fa-id-card" style="margin-right: 0.5rem;"></i>
                        {{ $company->registration_number }}
                    </p>
                    <div style="display: flex; gap: 0.5rem;">
                        @if($company->is_active)
                        <span class="badge success">Active</span>
                        @else
                        <span class="badge danger">Inactive</span>
                        @endif
                        <span class="badge primary">{{ $company->customers_count }} client(s)</span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary);">
                        <i class="fas fa-address-book"></i> Informations de Contact
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Email</div>
                            <div style="font-weight: 600;">{{ $company->email }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Téléphone</div>
                            <div style="font-weight: 600;">{{ $company->phone }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Adresse</div>
                            <div style="font-weight: 600;">{{ $company->address }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Localisation</div>
                            <div style="font-weight: 600;">{{ $company->city }}, {{ $company->country }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary);">
                        <i class="fas fa-wallet"></i> Informations Financières
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Limite de crédit</div>
                            <div style="font-weight: 800; font-size: 1.5rem; color: var(--primary);">
                                {{ number_format($company->credit_limit, 0, ',', ' ') }} GNF
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Date de création</div>
                            <div style="font-weight: 600;">{{ $company->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Dernière modification</div>
                            <div style="font-weight: 600;">{{ $company->updated_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($company->customers_count > 0)
            <div style="padding-top: 2rem; border-top: 1px solid var(--border);">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary);">
                    <i class="fas fa-users"></i> Clients ({{ $company->customers_count }})
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                    @foreach($company->customers as $customer)
                    <div style="padding: 1rem; background: var(--light); border-radius: 8px; border: 1px solid var(--border);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $customer->name }}</div>
                        <div style="font-size: 0.85rem; color: var(--gray);">{{ $customer->email }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>

            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Modifier
            </a>

            @if($company->is_active)
            <a href="{{ route('admin.companies.deactivate', $company) }}" class="btn btn-outline" style="color: var(--warning);" onclick="event.preventDefault(); if(confirm('Désactiver cette entreprise ?')) document.getElementById('deactivate-form').submit();">
                <i class="fas fa-ban"></i>
                Désactiver
            </a>
            <form id="deactivate-form" action="{{ route('admin.companies.deactivate', $company) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @else
            <a href="{{ route('admin.companies.activate', $company) }}" class="btn btn-outline" style="color: var(--success);" onclick="event.preventDefault(); document.getElementById('activate-form').submit();">
                <i class="fas fa-check"></i>
                Activer
            </a>
            <form id="activate-form" action="{{ route('admin.companies.activate', $company) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @endif

            @if($company->customers_count === 0)
            <a href="{{ route('admin.companies.destroy', $company) }}" class="btn btn-outline" style="color: var(--danger);" onclick="event.preventDefault(); if(confirm('Supprimer définitivement cette entreprise ?')) document.getElementById('delete-form').submit();">
                <i class="fas fa-trash"></i>
                Supprimer
            </a>
            <form id="delete-form" action="{{ route('admin.companies.destroy', $company) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
