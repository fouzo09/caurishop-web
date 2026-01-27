@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Détails du Client</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.customers.index') }}">Clients</a>
                <i class="fas fa-chevron-right"></i>
                <span>Détails</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div style="display: flex; align-items: start; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--border);">
                <div style="width: 80px; height: 80px; background: var(--primary); color: var(--white); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 2rem;">
                    @if($customer->type === 'individual')
                    <i class="fas fa-user"></i>
                    @else
                    <i class="fas fa-building"></i>
                    @endif
                </div>
                <div style="flex: 1;">
                    <h2 style="margin-bottom: 0.5rem;">{{ $customer->displayName() }}</h2>
                    <p style="color: var(--gray); margin-bottom: 0.75rem;">
                        <i class="fas fa-envelope" style="margin-right: 0.5rem;"></i>
                        {{ $customer->email }}
                    </p>
                    <div style="display: flex; gap: 0.5rem;">
                        @if($customer->is_active)
                        <span class="badge success">Actif</span>
                        @else
                        <span class="badge danger">Inactif</span>
                        @endif

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

                        @if($customer->company)
                        <span class="badge primary">{{ $customer->company->name }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary);">
                        <i class="fas fa-address-book"></i> Informations de Contact
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        @if($customer->type === 'individual')
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Prénom</div>
                            <div style="font-weight: 600;">{{ $customer->first_name }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Nom</div>
                            <div style="font-weight: 600;">{{ $customer->last_name }}</div>
                        </div>
                        @else
                        @if($customer->company)
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Entreprise</div>
                            <div style="font-weight: 600;">{{ $customer->company->name }}</div>
                        </div>
                        @endif
                        @if($customer->company_contact_name)
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Contact</div>
                            <div style="font-weight: 600;">{{ $customer->company_contact_name }}</div>
                        </div>
                        @endif
                        @endif
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Email</div>
                            <div style="font-weight: 600;">{{ $customer->email }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Téléphone</div>
                            <div style="font-weight: 600;">{{ $customer->phone }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Adresse</div>
                            <div style="font-weight: 600;">{{ $customer->address }}</div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: var(--primary);">
                        <i class="fas fa-chart-line"></i> Activité
                    </h3>
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Commandes</div>
                            <div style="font-weight: 800; font-size: 1.5rem; color: var(--primary);">
                                {{ $customer->orders->count() }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Paiements</div>
                            <div style="font-weight: 800; font-size: 1.5rem; color: var(--success);">
                                {{ $customer->payments->count() }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Date de création</div>
                            <div style="font-weight: 600;">{{ $customer->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.85rem; color: var(--gray); margin-bottom: 0.25rem;">Dernière modification</div>
                            <div style="font-weight: 600;">{{ $customer->updated_at->format('d/m/Y à H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer" style="display: flex; gap: 1rem; justify-content: flex-end;">
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>
                Retour
            </a>

            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i>
                Modifier
            </a>

            @if($customer->is_active)
            <a href="{{ route('admin.customers.deactivate', $customer) }}" class="btn btn-outline" style="color: var(--warning);" onclick="event.preventDefault(); if(confirm('Désactiver ce client ?')) document.getElementById('deactivate-form').submit();">
                <i class="fas fa-ban"></i>
                Désactiver
            </a>
            <form id="deactivate-form" action="{{ route('admin.customers.deactivate', $customer) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @else
            <a href="{{ route('admin.customers.activate', $customer) }}" class="btn btn-outline" style="color: var(--success);" onclick="event.preventDefault(); document.getElementById('activate-form').submit();">
                <i class="fas fa-check"></i>
                Activer
            </a>
            <form id="activate-form" action="{{ route('admin.customers.activate', $customer) }}" method="POST" style="display: none;">
                @csrf
            </form>
            @endif

            @if($customer->orders->count() === 0)
            <a href="{{ route('admin.customers.destroy', $customer) }}" class="btn btn-outline" style="color: var(--danger);" onclick="event.preventDefault(); if(confirm('Supprimer définitivement ce client ?')) document.getElementById('delete-form').submit();">
                <i class="fas fa-trash"></i>
                Supprimer
            </a>
            <form id="delete-form" action="{{ route('admin.customers.destroy', $customer) }}" method="POST" style="display: none;">
                @csrf
                @method('DELETE')
            </form>
            @endif
        </div>
    </div>
</div>
@endsection
