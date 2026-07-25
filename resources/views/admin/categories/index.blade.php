@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Catégories</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Catégories</span>
            </div>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle catégorie
        </a>
    </div>

    @if(session('success'))
    <div class="alert success"><i class="fas fa-check-circle"></i> <span>{{ session('success') }}</span></div>
    @endif

    <div class="card">
        @if($categories->isEmpty())
        <div class="card-body" style="text-align:center;padding:3rem;color:var(--gray);">
            <i class="fas fa-tags" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
            <p>Aucune catégorie. <a href="{{ route('admin.categories.create') }}">Créer la première</a></p>
        </div>
        @else
        <div class="card-body" style="padding:0;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:60px;">Ordre</th>
                        <th>Nom</th>
                        <th>Slug</th>
                        <th>Produits</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td style="color:var(--gray);">{{ $category->sort_order }}</td>
                        <td>
                            <div style="font-weight:600;">
                                @if($category->icon)<i class="{{ str_starts_with($category->icon, 'fa') ? 'fas ' . $category->icon : $category->icon }}" style="opacity:.6;width:18px;"></i>@endif
                                {{ $category->name }}
                            </div>
                            @if($category->description)
                            <div style="font-size:.75rem;color:var(--gray);">{{ Str::limit($category->description, 70) }}</div>
                            @endif
                        </td>
                        <td style="font-size:.85rem;color:var(--gray);">{{ $category->slug }}</td>
                        <td>
                            <span class="badge {{ $category->products_count > 0 ? 'success' : 'secondary' }}">
                                {{ $category->products_count }} produit(s)
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('admin.categories.toggle', $category) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="badge {{ $category->is_active ? 'success' : 'secondary' }}"
                                        style="border:none;cursor:pointer;">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                  style="display:inline;"
                                  onsubmit="return confirm('Supprimer cette catégorie ? Les produits liés seront décatégorisés.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
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
