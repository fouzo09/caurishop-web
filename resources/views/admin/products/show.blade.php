@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">{{ $product->name }}</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.products.index') }}">Produits</a>
                <i class="fas fa-chevron-right"></i>
                <span>{{ $product->name }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit"></i> Modifier
            </a>
            @if($product->is_published)
            <form action="{{ route('admin.products.unpublish', $product) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">
                    <i class="fas fa-eye-slash"></i> Dépublier
                </button>
            </form>
            @else
            <form action="{{ route('admin.products.publish', $product) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color: var(--success);">
                    <i class="fas fa-eye"></i> Publier
                </button>
            </form>
            @endif
            @if($product->is_active)
            <form action="{{ route('admin.products.deactivate', $product) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger);">
                    <i class="fas fa-ban"></i> Désactiver
                </button>
            </form>
            @else
            <form action="{{ route('admin.products.activate', $product) }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm" style="color: var(--success);">
                    <i class="fas fa-check"></i> Activer
                </button>
            </form>
            @endif
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

    @if($product->isVariable() && $product->variants->isEmpty())
    <div class="alert warning" style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Ce produit n'a aucune variante. Il ne peut pas être publié ni commandé tant qu'aucune variante n'est ajoutée.</span>
        </div>
        <a href="{{ route('admin.products.variants.create', $product) }}" class="btn btn-primary btn-sm" style="white-space: nowrap;">
            <i class="fas fa-plus"></i> Ajouter une variante
        </a>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">

        {{-- Informations principales --}}
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informations du produit</h3>
                </div>
                <div class="card-body">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">Nom</div>
                            <div style="font-weight: 600;">{{ $product->name }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">SKU</div>
                            <div style="font-family: monospace;">{{ $product->sku ?? '—' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">Type</div>
                            @if($product->isSimple())
                            <span class="badge" style="background: rgba(59,130,246,0.1); color:#3B82F6;">
                                <i class="fas fa-box"></i> Simple
                            </span>
                            @else
                            <span class="badge" style="background: rgba(139,92,246,0.1); color:#8B5CF6;">
                                <i class="fas fa-boxes"></i> Variable
                            </span>
                            @endif
                        </div>
                        @if($product->isSimple())
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">Prix</div>
                            <div style="font-weight: 600; color: var(--primary);">
                                {{ number_format($product->price, 0, ',', ' ') }} GNF
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">Stock</div>
                            @if($product->stock_quantity > 0)
                            <span class="badge success">{{ $product->stock_quantity }} unités</span>
                            @else
                            <span class="badge danger">Rupture de stock</span>
                            @endif
                        </div>
                        @endif
                        <div>
                            <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.25rem;">Slug</div>
                            <div style="font-size: 0.85rem; color: var(--gray);">{{ $product->slug ?? '—' }}</div>
                        </div>
                    </div>

                    @if($product->description)
                    <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                        <div style="font-size: 0.8rem; color: var(--gray); margin-bottom: 0.5rem;">Description</div>
                        <p style="line-height: 1.6; color: var(--dark);">{{ $product->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Variantes (produit variable uniquement) --}}
            @if($product->isVariable())
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Variantes ({{ $product->variants->count() }})</h3>
                    <a href="{{ route('admin.products.variants.create', $product) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Ajouter une variante
                    </a>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-container">
                        <table>
                            <thead>
                            <tr>
                                <th>Variante</th>
                                <th>SKU</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Crédit</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($product->variants as $variant)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $variant->name }}</div>
                                    @if($variant->attributes)
                                    <div style="font-size: 0.8rem; color: var(--gray);">
                                        @foreach($variant->attributes as $key => $value)
                                        <span>{{ ucfirst($key) }}: {{ $value }}</span>
                                        @if(!$loop->last) &nbsp;·&nbsp; @endif
                                        @endforeach
                                    </div>
                                    @endif
                                </td>
                                <td style="font-family: monospace; font-size: 0.85rem;">{{ $variant->sku }}</td>
                                <td style="font-weight: 600; color: var(--primary);">
                                    {{ number_format($variant->price, 0, ',', ' ') }} GNF
                                </td>
                                <td>
                                    @if($variant->stock_quantity > 0)
                                    <span class="badge success">{{ $variant->stock_quantity }}</span>
                                    @else
                                    <span class="badge danger">Rupture</span>
                                    @endif
                                </td>
                                <td>
                                    @if($variant->effectiveCreditEnabled())
                                    <span class="badge success">
                                        {{ $variant->effectiveCreditInstallmentsCount() }}x
                                        @if($variant->credit_enabled)
                                        <small style="opacity:0.7;">(override)</small>
                                        @endif
                                    </span>
                                    @else
                                    <span class="badge" style="background: var(--light); color: var(--gray);">Non</span>
                                    @endif
                                </td>
                                <td>
                                    @if($variant->is_active)
                                    <span class="badge success">Actif</span>
                                    @else
                                    <span class="badge danger">Inactif</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="{{ route('admin.products.variants.edit', [$product, $variant]) }}" class="btn btn-outline btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger);"
                                            onclick="openDeleteVariantModal({{ $variant->id }}, '{{ $variant->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <form id="delete-variant-{{ $variant->id }}" action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 2rem; color: var(--gray);">
                                    Aucune variante. <a href="{{ route('admin.products.variants.create', $product) }}">Ajouter la première</a>
                                </td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Panneau latéral --}}
        <div>
            {{-- Images --}}
            <div class="card" style="margin-bottom: 1.5rem;">
                <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <h3 class="card-title">Images ({{ $product->images->count() }})</h3>
                    <a href="{{ route('admin.products.edit', $product) }}#images" class="btn btn-outline btn-sm">
                        <i class="fas fa-plus"></i> Gérer
                    </a>
                </div>
                <div class="card-body" style="padding: 0.75rem;">
                    @if($product->images->isEmpty())
                    <div style="text-align:center;padding:1.5rem;color:var(--gray);font-size:0.85rem;">
                        <i class="fas fa-images" style="display:block;font-size:1.5rem;margin-bottom:.4rem;opacity:.3;"></i>
                        Aucune image
                    </div>
                    @else
                    @php $primary = $product->images->firstWhere('is_primary', true) ?? $product->images->first(); @endphp
                    <img src="{{ $primary->url }}" alt="{{ $product->name }}"
                         style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:6px;margin-bottom:.75rem;">
                    @if($product->images->count() > 1)
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        @foreach($product->images as $img)
                        <div style="width:52px;height:52px;border-radius:5px;overflow:hidden;border:2px solid {{ $img->is_primary ? 'var(--primary)' : 'var(--border)' }};">
                            <img src="{{ $img->url }}" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        @endforeach
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statut</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Publication</span>
                            @if($product->is_published)
                            <span class="badge success">Publié</span>
                            @else
                            <span class="badge warning">Brouillon</span>
                            @endif
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Activation</span>
                            @if($product->is_active)
                            <span class="badge success">Actif</span>
                            @else
                            <span class="badge danger">Inactif</span>
                            @endif
                        </div>
                        @if($product->isVariable())
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Stock total</span>
                            <span style="font-weight: 600;">{{ $product->totalStock() }} unités</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Crédit</h3>
                </div>
                <div class="card-body">
                    @if($product->credit_enabled)
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Crédit activé</span>
                            <span class="badge success">Oui</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Durée max</span>
                            <span style="font-weight: 600;">{{ $product->credit_duration_months }} mois</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Mensualités</span>
                            <span style="font-weight: 600;">{{ $product->credit_installments_count }}x</span>
                        </div>
                        @if($product->isSimple())
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-size: 0.9rem; color: var(--gray);">Mensualité</span>
                            <span style="font-weight: 600; color: var(--primary);">{{ $product->displayMonthlyPayment() }}</span>
                        </div>
                        @endif
                    </div>
                    @else
                    <p style="color: var(--gray); font-size: 0.9rem; margin: 0;">Paiement à crédit non activé.</p>
                    @endif
                </div>
            </div>

            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body" style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline btn-sm" style="text-align:center;">
                        <i class="fas fa-edit"></i> Modifier le produit
                    </a>
                    <button type="button" class="btn btn-outline btn-sm" style="color: var(--danger); text-align:center;"
                        onclick="openDeleteModal({{ $product->id }}, '{{ $product->name }}')">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                    <form id="delete-form-{{ $product->id }}" action="{{ route('admin.products.destroy', $product) }}" method="POST" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal suppression produit --}}
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

{{-- Modal suppression variante --}}
<div id="deleteVariantModal" class="modal-overlay">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle"></i> Supprimer la variante</h3>
            <button class="close-btn" onclick="closeModal('deleteVariantModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p>Voulez-vous vraiment supprimer la variante <strong id="deleteVariantName"></strong> ?</p>
            <div class="info-box danger">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Cette action est irréversible.</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('deleteVariantModal')">Annuler</button>
            <button class="btn btn-primary" style="background: var(--danger);" onclick="confirmDeleteVariant()">Supprimer</button>
        </div>
    </div>
</div>

<style>
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
    .modal-overlay.active { display: flex; }
    .modal-dialog { background: var(--white); border-radius: 8px; width: 90%; max-width: 450px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
    .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }
    .close-btn { background: none; border: none; font-size: 1.5rem; color: var(--gray); cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 4px; }
    .close-btn:hover { background: var(--light); color: var(--dark); }
    .modal-body { padding: 1.5rem; }
    .modal-body p { margin-bottom: 1rem; line-height: 1.6; }
    .info-box { padding: 0.75rem 1rem; border-radius: 6px; display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; }
    .info-box.danger { background: rgba(239,68,68,0.1); color: var(--danger); border-left: 3px solid var(--danger); }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; gap: 0.75rem; justify-content: flex-end; }
</style>

<script>
    let currentProductId = null;
    let currentVariantId = null;

    function openDeleteModal(id, name) {
        currentProductId = id;
        document.getElementById('deleteProductName').textContent = name;
        document.getElementById('deleteModal').classList.add('active');
    }

    function openDeleteVariantModal(id, name) {
        currentVariantId = id;
        document.getElementById('deleteVariantName').textContent = name;
        document.getElementById('deleteVariantModal').classList.add('active');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
        currentProductId = null;
        currentVariantId = null;
    }

    function confirmDelete() {
        if (currentProductId) {
            document.getElementById('delete-form-' + currentProductId).submit();
        }
    }

    function confirmDeleteVariant() {
        if (currentVariantId) {
            document.getElementById('delete-variant-' + currentVariantId).submit();
        }
    }

    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('active');
            currentProductId = null;
            currentVariantId = null;
        }
    }
</script>
@endsection
