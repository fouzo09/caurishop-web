@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Marges</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <span>Marges</span>
            </div>
        </div>
        <div style="display:flex;gap:.75rem;">
            <form action="{{ route('admin.margins.recalculate') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline"
                        onclick="return confirm('Recalculer les prix de tous les produits ayant un prix fournisseur ?')"
                        title="Applique les marges actuelles sur tous les produits">
                    <i class="fas fa-sync-alt"></i> Recalculer tous les prix
                </button>
            </form>
        </div>
    </div>

    {{-- Info --}}
    <div class="alert" style="background:rgba(0,102,255,.06);border-color:rgba(0,102,255,.2);color:var(--dark);">
        <i class="fas fa-info-circle" style="color:var(--primary);"></i>
        <span>
            Lorsqu'un produit a un <strong>prix fournisseur</strong>, son prix client est automatiquement calculé selon la marge applicable.<br>
            Priorité : <strong>marge produit spécifique</strong> &gt; <strong>marge globale</strong>.
        </span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start;">

        {{-- ── Marge globale ── --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-globe" style="color:var(--primary);"></i> Marge Globale</h3>
                @if($globalMargin)
                <span class="badge {{ $globalMargin->is_active ? 'success' : 'secondary' }}">
                    {{ $globalMargin->is_active ? 'Active' : 'Inactive' }}
                </span>
                @endif
            </div>
            <div class="card-body">
                <p style="font-size:.85rem;color:var(--gray);margin-bottom:1.25rem;">
                    S'applique à tous les produits sauf ceux ayant une marge spécifique.
                </p>

                <form action="{{ route('admin.margins.global.store') }}" method="POST">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-input" onchange="updateGlobalPreview()">
                                <option value="percentage" {{ ($globalMargin?->type ?? 'percentage') === 'percentage' ? 'selected' : '' }}>
                                    Pourcentage (%)
                                </option>
                                <option value="fixed" {{ ($globalMargin?->type) === 'fixed' ? 'selected' : '' }}>
                                    Montant fixe (GNF)
                                </option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Valeur</label>
                            <input type="number" name="value" class="form-input" id="globalValue"
                                   value="{{ old('value', $globalMargin?->value ?? '') }}"
                                   step="0.01" min="0" placeholder="Ex: 10" required
                                   oninput="updateGlobalPreview()">
                        </div>
                        <div class="form-group" style="margin:0;grid-column:span 2;">
                            <label class="form-label">Libellé (optionnel)</label>
                            <input type="text" name="label" class="form-input"
                                   value="{{ old('label', $globalMargin?->label ?? '') }}"
                                   placeholder="Ex: Marge standard CauriShop">
                        </div>
                    </div>

                    <div id="globalPreview" style="display:none;background:rgba(0,102,255,.06);border-radius:6px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;">
                        <i class="fas fa-calculator" style="color:var(--primary);"></i>
                        Exemple — Fournisseur : <strong>100 000 GNF</strong> → Client : <strong id="globalPreviewValue">—</strong>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:.5rem;">
                        <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                {{ old('is_active', $globalMargin?->is_active ?? true) ? 'checked' : '' }}>
                            <span style="font-size:.9rem;">Active</span>
                        </label>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i>
                            {{ $globalMargin ? 'Mettre à jour' : 'Créer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ── Marge par produit ── --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box" style="color:var(--primary);"></i> Marge par Produit</h3>
            </div>
            <div class="card-body">
                <p style="font-size:.85rem;color:var(--gray);margin-bottom:1.25rem;">
                    Surcharge la marge globale pour un produit donné.
                </p>

                <form action="{{ route('admin.margins.product.store') }}" method="POST">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                        <div class="form-group" style="margin:0;grid-column:span 2;">
                            <label class="form-label">Produit</label>
                            <select name="reference_id" class="form-input" required>
                                <option value="">— Sélectionner un produit —</option>
                                @foreach(\App\Models\Product::orderBy('name')->get() as $p)
                                <option value="{{ $p->id }}" {{ old('reference_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-input">
                                <option value="percentage" {{ old('type', 'percentage') === 'percentage' ? 'selected' : '' }}>%</option>
                                <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>GNF fixe</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Valeur</label>
                            <input type="number" name="value" class="form-input"
                                   value="{{ old('value') }}" step="0.01" min="0" placeholder="Ex: 15" required>
                        </div>
                        <div class="form-group" style="margin:0;grid-column:span 2;">
                            <label class="form-label">Libellé (optionnel)</label>
                            <input type="text" name="label" class="form-input"
                                   value="{{ old('label') }}" placeholder="Ex: Marge spéciale iPhone">
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── Liste des marges produit ── --}}
    @if($productMargins->isNotEmpty())
    <div class="card" style="margin-top:1.5rem;">
        <div class="card-header">
            <h3 class="card-title">Marges Produit Configurées</h3>
            <span class="badge secondary">{{ $productMargins->count() }}</span>
        </div>
        <div class="card-body" style="padding:0;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Type</th>
                        <th>Valeur</th>
                        <th>Libellé</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productMargins as $margin)
                    <tr>
                        <td>
                            <strong>{{ $products[$margin->reference_id] ?? '—' }}</strong>
                            @if($margin->reference_id)
                            <a href="{{ route('admin.products.show', $margin->reference_id) }}"
                               style="font-size:.75rem;color:var(--primary);margin-left:.5rem;">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $margin->type === 'percentage' ? 'info' : 'warning' }}">
                                {{ $margin->type === 'percentage' ? 'Pourcentage' : 'Fixe GNF' }}
                            </span>
                        </td>
                        <td><strong>{{ $margin->displayValue() }}</strong></td>
                        <td style="color:var(--gray);font-size:.85rem;">{{ $margin->label ?? '—' }}</td>
                        <td>
                            <form action="{{ route('admin.margins.toggle', $margin) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="badge {{ $margin->is_active ? 'success' : 'secondary' }}"
                                        style="border:none;cursor:pointer;" title="Cliquer pour basculer">
                                    {{ $margin->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td style="text-align:right;">
                            <button onclick="openEditModal({{ $margin->id }}, '{{ $margin->type }}', {{ $margin->value }}, '{{ $margin->label }}')"
                                    class="btn btn-outline btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.margins.destroy', $margin) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Supprimer cette marge ?')">
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
    </div>
    @endif
</div>

{{-- Modal d'édition d'une marge produit --}}
<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;padding:1.5rem;width:420px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="margin:0 0 1.25rem;font-size:1rem;">Modifier la marge</h3>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div style="display:grid;gap:1rem;">
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Type</label>
                    <select name="type" id="editType" class="form-input">
                        <option value="percentage">Pourcentage (%)</option>
                        <option value="fixed">Montant fixe (GNF)</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Valeur</label>
                    <input type="number" name="value" id="editValue" class="form-input" step="0.01" min="0" required>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Libellé</label>
                    <input type="text" name="label" id="editLabel" class="form-input">
                </div>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem;">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
const baseUpdateUrl = '{{ url("admin/margins") }}/';

function openEditModal(id, type, value, label) {
    document.getElementById('editForm').action = baseUpdateUrl + id;
    document.getElementById('editType').value = type;
    document.getElementById('editValue').value = value;
    document.getElementById('editLabel').value = label;
    const modal = document.getElementById('editModal');
    modal.style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

function updateGlobalPreview() {
    const typeEl  = document.querySelector('select[name="type"]');
    const valueEl = document.getElementById('globalValue');
    const preview = document.getElementById('globalPreview');
    const result  = document.getElementById('globalPreviewValue');

    const v = parseFloat(valueEl.value);
    if (!v || v <= 0) { preview.style.display = 'none'; return; }

    const base = 100000;
    let computed;
    if (typeEl.value === 'percentage') {
        computed = Math.round(base * (1 + v / 100));
    } else {
        computed = base + v;
    }

    result.textContent = new Intl.NumberFormat('fr-FR').format(computed) + ' GNF';
    preview.style.display = 'block';
}

// Init preview au chargement
updateGlobalPreview();
</script>
@endsection
