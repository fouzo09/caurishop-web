@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Produits</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Produits</span>
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
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Produits</div>
                <div class="stat-value">{{ $products->total() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Publiés</div>
                <div class="stat-value">{{ $products->where('is_published', true)->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Produits Simples</div>
                <div class="stat-value">{{ $products->where('type', 'simple')->count() }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Produits Variables</div>
                <div class="stat-value">{{ $products->where('type', 'variable')->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i>
                Nouveau Produit
            </a>
        </div>

        {{-- Filtres --}}
        <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); background: var(--light);">
            <form method="GET" action="{{ route('admin.products.index') }}" style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
                <div>
                    <input type="text" name="search" class="form-input" placeholder="Rechercher (nom, SKU...)"
                        value="{{ request('search') }}" style="min-width: 220px;">
                </div>
                <div>
                    <select name="type" class="form-input">
                        <option value="">Tous les types</option>
                        <option value="simple" {{ request('type') === 'simple' ? 'selected' : '' }}>Simple</option>
                        <option value="variable" {{ request('type') === 'variable' ? 'selected' : '' }}>Variable</option>
                    </select>
                </div>
                <div>
                    <select name="is_published" class="form-input">
                        <option value="">Publication</option>
                        <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Publié</option>
                        <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Brouillon</option>
                    </select>
                </div>
                <div>
                    <select name="is_active" class="form-input">
                        <option value="">Statut</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div>
                    <select name="credit_enabled" class="form-input">
                        <option value="">Crédit</option>
                        <option value="1" {{ request('credit_enabled') === '1' ? 'selected' : '' }}>Avec crédit</option>
                        <option value="0" {{ request('credit_enabled') === '0' ? 'selected' : '' }}>Sans crédit</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                @if(request()->hasAny(['search', 'type', 'is_published', 'is_active', 'credit_enabled']))
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Prix</th>
                        <th>Stock</th>
                        <th>Crédit</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>#{{ $product->id }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $product->name }}</div>
                            <div style="font-size: 0.8rem; color: var(--gray);">SKU: {{ $product->sku }}</div>
                        </td>
                        <td>
                            @if($product->type === 'simple')
                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3B82F6;">
                                <i class="fas fa-box"></i>
                                Simple
                            </span>
                            @else
                            <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                                <i class="fas fa-boxes"></i>
                                Variable ({{ $product->variants->count() }})
                            </span>
                            @endif
                        </td>
                        <td>
                            @if($product->isSimple())
                            <span style="font-weight: 600; color: var(--primary);">
                                {{ number_format($product->price, 0, ',', ' ') }} GNF
                            </span>
                            @else
                            <span style="font-size: 0.85rem; color: var(--gray);">Varie</span>
                            @endif
                        </td>
                        <td>
                            @if($product->isSimple())
                                @if($product->stock_quantity > 0)
                                <span class="badge success">{{ $product->stock_quantity }}</span>
                                @else
                                <span class="badge danger">Rupture</span>
                                @endif
                            @else
                                @php $totalStock = $product->variants->sum('stock_quantity'); @endphp
                                @if($totalStock > 0)
                                <span class="badge success">{{ $totalStock }}</span>
                                @else
                                <span class="badge danger">Rupture</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($product->credit_enabled)
                            <span class="badge success">
                                <i class="fas fa-credit-card"></i>
                                {{ $product->credit_installments_count }}x
                            </span>
                            @else
                            <span class="badge" style="background: var(--light); color: var(--gray);">Non</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                @if($product->is_published)
                                <span class="badge success">Publié</span>
                                @else
                                <span class="badge warning">Brouillon</span>
                                @endif

                                @if($product->is_active)
                                <span class="badge success">Actif</span>
                                @else
                                <span class="badge danger">Inactif</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);" onclick="openDeleteModal({{ $product->id }}, '{{ $product->name }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $product->id }}" action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 3rem; color: var(--gray);">
                            <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 1rem;"></i>
                            Aucun produit trouvé
                        </td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Supprimer -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer le produit</h3>
            <button class="close-btn" onclick="closeModal('deleteModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer <strong id="deleteProductName"></strong> ?</p>
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
    let currentProductId = null;

    function openDeleteModal(productId, productName) {
        currentProductId = productId;
        document.getElementById('deleteProductName').textContent = productName;
        document.getElementById('deleteModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentProductId = null;
    }

    function confirmDelete() {
        if (currentProductId) {
            document.getElementById('delete-form-' + currentProductId).submit();
        }
    }

    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            currentProductId = null;
        }
    }
</script>
@endsection
