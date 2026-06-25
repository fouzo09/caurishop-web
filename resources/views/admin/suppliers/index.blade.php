@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Fournisseurs</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Fournisseurs</span>
            </div>
        </div>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau fournisseur
        </a>
    </div>

    @if(session('success'))
    <div class="alert success"><i class="fas fa-check-circle"></i> <span>{{ session('success') }}</span></div>
    @endif

    <div class="card">
        @if($suppliers->isEmpty())
        <div class="card-body" style="text-align:center;padding:3rem;color:var(--gray);">
            <i class="fas fa-truck" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            <p>Aucun fournisseur. <a href="{{ route('admin.suppliers.create') }}">Créer le premier</a></p>
        </div>
        @else
        <div class="card-body" style="padding:0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Pays</th>
                        <th>Produits</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $supplier->name }}</div>
                            @if($supplier->website)
                            <a href="{{ $supplier->website }}" target="_blank" rel="noopener"
                               style="font-size:.75rem;color:var(--gray);">
                                <i class="fas fa-external-link-alt"></i> {{ parse_url($supplier->website, PHP_URL_HOST) }}
                            </a>
                            @endif
                        </td>
                        <td style="font-size:.85rem;">
                            @if($supplier->email)
                            <div><i class="fas fa-envelope" style="opacity:.5;width:14px;"></i> {{ $supplier->email }}</div>
                            @endif
                            @if($supplier->phone)
                            <div><i class="fas fa-phone" style="opacity:.5;width:14px;"></i> {{ $supplier->phone }}</div>
                            @endif
                        </td>
                        <td style="font-size:.85rem;">{{ $supplier->country ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $supplier->products_count > 0 ? 'success' : 'secondary' }}">
                                {{ $supplier->products_count }} produit(s)
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.suppliers.toggle', $supplier) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="badge {{ $supplier->is_active ? 'success' : 'secondary' }}"
                                        style="border:none;cursor:pointer;">
                                    {{ $supplier->is_active ? 'Actif' : 'Inactif' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.suppliers.destroy', $supplier) }}" method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Supprimer ce fournisseur ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        {{ $supplier->products_count > 0 ? 'disabled title=Fournisseur utilisé par des produits' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
