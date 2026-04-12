@extends('tpl.company')

@section('content')
<div class="page-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Employés</h1>
            <p style="color: var(--gray); margin-top: 0.25rem; font-size: 0.9rem;">{{ $company->name }}</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Profil acheteur</th>
                        <th>Dernière commande</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($userEmployees as $employee)
                @php $customer = $employee->customer; @endphp
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div class="user-avatar" style="width: 32px; height: 32px; font-size: 0.75rem; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                {{ strtoupper(substr($employee->name, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-weight: 600;">{{ $employee->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="color: var(--gray);">{{ $employee->email }}</td>
                    <td>
                        @if($customer)
                        <span class="badge badge-success"><i class="fas fa-check"></i> Créé</span>
                        @else
                        <span class="badge badge-secondary">Aucun</span>
                        @endif
                    </td>
                    <td>
                        @if($customer && $customer->orders->isNotEmpty())
                            {{ $customer->orders->first()->created_at->format('d/m/Y') }}
                        @else
                            <span style="color: var(--gray);">—</span>
                        @endif
                    </td>
                    <td>
                        @if($employee->is_active)
                        <span class="badge badge-success">Actif</span>
                        @else
                        <span class="badge badge-danger">Inactif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--gray);">
                        <i class="fas fa-users" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                        Aucun employé trouvé
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($customers->isNotEmpty())
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-tag"></i> Profils acheteurs ({{ $customers->count() }})</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Plafond crédit</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($customers as $customer)
                <tr>
                    <td><strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong></td>
                    <td style="color: var(--gray);">{{ $customer->email }}</td>
                    <td style="color: var(--gray);">{{ $customer->phone ?? '—' }}</td>
                    <td>
                        @if($customer->credit_limit)
                        {{ number_format($customer->credit_limit, 0, ',', ' ') }} GNF
                        @else
                        <span style="color: var(--gray);">Non défini</span>
                        @endif
                    </td>
                    <td>
                        @if($customer->is_active)
                        <span class="badge badge-success">Actif</span>
                        @else
                        <span class="badge badge-danger">Inactif</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
