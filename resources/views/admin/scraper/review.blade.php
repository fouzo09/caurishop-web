@extends('tpl.admin')

@section('content')
<div class="content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Révision des produits</h1>
            <div class="page-breadcrumb">
                <span>CAURISHOP</span>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.scraper.index') }}">Scraper</a>
                <i class="fas fa-chevron-right"></i>
                <a href="{{ route('admin.scraper.show', $filename) }}">Résultats</a>
                <i class="fas fa-chevron-right"></i>
                <span>Révision</span>
            </div>
        </div>
    </div>

    @if(session('error'))
    <div class="alert danger"><i class="fas fa-exclamation-triangle"></i> <span>{{ session('error') }}</span></div>
    @endif

    <form action="{{ route('admin.scraper.create-products', $filename) }}" method="POST" id="reviewForm">
        @csrf
        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">

        {{-- Barre supérieure --}}
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card-body" style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;padding:1rem 1.5rem;">
                <div style="flex:1;">
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Fournisseur</div>
                    <div style="font-size:1rem;font-weight:700;">{{ $supplier->name }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Groupes</div>
                    <div style="font-size:1rem;font-weight:700;">{{ count($groups) }}</div>
                </div>
                <div>
                    <div style="font-size:.75rem;color:var(--gray);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.2rem;">Complétude moy.</div>
                    <div style="font-size:1rem;font-weight:700;">
                        {{ count($groups) ? round(array_sum(array_column($groups, 'completeness')) / count($groups)) : 0 }}%
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-left:auto;">
                    <i class="fas fa-plus-circle"></i> Créer {{ count($groups) }} produit(s)
                </button>
            </div>
        </div>

        {{-- Cartes de groupes --}}
        @foreach($groups as $gi => $group)
        @php
            $isVariable = $group['type'] === 'variable' && count($group['variants']) > 0;
            $completeness = $group['completeness'];
            $missing = $group['missing_fields'];
            $badgeColor = $completeness >= 80 ? '#16a34a' : ($completeness >= 50 ? '#d97706' : '#dc2626');
        @endphp
        <div class="card group-card" style="margin-bottom:1.5rem;" data-index="{{ $gi }}">
            {{-- En-tête --}}
            <div style="display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;border-bottom:1px solid var(--border);">
                {{-- Thumbnail --}}
                <div style="width:64px;height:64px;border-radius:8px;background:var(--light);overflow:hidden;flex-shrink:0;">
                    @if(!empty($group['image_url']))
                    <img src="{{ route('admin.scraper.image-proxy', ['url' => $group['image_url']]) }}"
                         style="width:100%;height:100%;object-fit:cover;"
                         onerror="this.style.display='none'"
                         loading="lazy">
                    @endif
                </div>

                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;">
                        <span style="font-size:.95rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px;">
                            {{ $group['name'] }}
                        </span>
                        {{-- Badge complétude --}}
                        <span style="font-size:.7rem;font-weight:700;padding:.2rem .5rem;border-radius:20px;color:#fff;background:{{ $badgeColor }};">
                            {{ $completeness }}%
                        </span>
                        {{-- Badge type --}}
                        <span class="type-badge-{{ $gi }}" style="font-size:.7rem;font-weight:600;padding:.2rem .5rem;border-radius:20px;background:{{ $isVariable ? '#ede9fe' : '#dbeafe' }};color:{{ $isVariable ? '#6d28d9' : '#1d4ed8' }};">
                            {{ $isVariable ? 'Variable' : 'Simple' }}
                        </span>
                    </div>
                    @if(!empty($missing))
                    <div style="font-size:.75rem;color:#d97706;margin-top:.25rem;">
                        <i class="fas fa-exclamation-circle"></i>
                        Champs manquants :
                        @foreach($missing as $f)
                            <span style="font-weight:600;">{{ ['name'=>'Nom','price'=>'Prix vente','supplier_price'=>'Prix fournisseur','description'=>'Description','image_url'=>'Image'][$f] ?? $f }}</span>{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Toggle expand --}}
                <button type="button" class="btn btn-outline btn-sm toggle-btn"
                        onclick="toggleCard({{ $gi }})" style="flex-shrink:0;">
                    <i class="fas fa-chevron-down" id="chevron-{{ $gi }}"></i>
                </button>
            </div>

            {{-- Corps (collapsible) --}}
            <div id="body-{{ $gi }}" style="padding:1.5rem;">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

                    {{-- Colonne gauche --}}
                    <div>
                        <div style="margin-bottom:1rem;">
                            <label class="form-label">Nom du produit <span style="color:#dc2626;">*</span></label>
                            <input type="text" name="groups[{{ $gi }}][name]"
                                   class="form-input {{ in_array('name', $missing) ? 'input-warning' : '' }}"
                                   value="{{ old("groups.$gi.name", $group['name']) }}" required>
                        </div>

                        <div style="margin-bottom:1rem;">
                            <label class="form-label">Description</label>
                            <textarea name="groups[{{ $gi }}][description]" class="form-input"
                                      rows="3" style="resize:vertical;">{{ old("groups.$gi.description", $group['description']) }}</textarea>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                            <div>
                                <label class="form-label">Prix fournisseur</label>
                                <input type="number" name="groups[{{ $gi }}][supplier_price]"
                                       class="form-input {{ in_array('supplier_price', $missing) ? 'input-warning' : '' }}"
                                       value="{{ old("groups.$gi.supplier_price", $group['supplier_price']) }}"
                                       step="0.01" min="0">
                            </div>
                            <div>
                                <label class="form-label">Prix de vente <span style="color:#dc2626;">*</span></label>
                                <input type="number" name="groups[{{ $gi }}][price]"
                                       class="form-input {{ in_array('price', $missing) ? 'input-warning' : '' }}"
                                       value="{{ old("groups.$gi.price", $group['price']) }}"
                                       step="0.01" min="0" required>
                            </div>
                        </div>

                        {{-- Type toggle --}}
                        <div style="margin-bottom:1rem;">
                            <label class="form-label">Type de produit</label>
                            <div style="display:flex;gap:.5rem;">
                                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.85rem;
                                              padding:.4rem .75rem;border-radius:6px;border:2px solid var(--border);
                                              transition:border-color .15s,background .15s;"
                                       id="lbl-simple-{{ $gi }}">
                                    <input type="radio" name="groups[{{ $gi }}][type]" value="simple"
                                           onchange="onTypeChange({{ $gi }})"
                                           {{ !$isVariable ? 'checked' : '' }}>
                                    Simple
                                </label>
                                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer;font-size:.85rem;
                                              padding:.4rem .75rem;border-radius:6px;border:2px solid var(--border);
                                              transition:border-color .15s,background .15s;"
                                       id="lbl-variable-{{ $gi }}">
                                    <input type="radio" name="groups[{{ $gi }}][type]" value="variable"
                                           onchange="onTypeChange({{ $gi }})"
                                           {{ $isVariable ? 'checked' : '' }}>
                                    Variable (déclinaisons)
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Colonne droite --}}
                    <div>
                        {{-- Aperçu image --}}
                        <div style="margin-bottom:1rem;">
                            <label class="form-label">Image</label>
                            <input type="hidden" name="groups[{{ $gi }}][image_url]"
                                   value="{{ $group['image_url'] }}" id="img-url-{{ $gi }}">
                            <div style="position:relative;width:100%;height:160px;border-radius:8px;background:var(--light);overflow:hidden;border:1px solid var(--border);">
                                @if(!empty($group['image_url']))
                                <img id="img-preview-{{ $gi }}"
                                     src="{{ route('admin.scraper.image-proxy', ['url' => $group['image_url']]) }}"
                                     style="width:100%;height:100%;object-fit:contain;"
                                     loading="lazy">
                                @else
                                <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--gray);opacity:.4;flex-direction:column;gap:.5rem;">
                                    <i class="fas fa-image fa-2x"></i>
                                    <span style="font-size:.75rem;">Pas d'image</span>
                                </div>
                                @endif
                            </div>
                            @if(!empty($group['source_url']))
                            <a href="{{ $group['source_url'] }}" target="_blank" rel="noopener"
                               style="font-size:.75rem;color:var(--gray);text-decoration:none;display:flex;align-items:center;gap:.3rem;margin-top:.4rem;">
                                <i class="fas fa-external-link-alt"></i> Voir sur le site source
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Section variantes --}}
                <div id="variants-section-{{ $gi }}" style="{{ !$isVariable ? 'display:none;' : '' }}">
                    <div style="border-top:1px solid var(--border);margin-top:.5rem;padding-top:1.25rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.75rem;">
                            <div style="font-size:.85rem;font-weight:700;">
                                <i class="fas fa-layer-group" style="color:var(--primary);margin-right:.35rem;"></i>
                                Déclinaisons
                                <span id="variant-count-{{ $gi }}" style="font-size:.75rem;color:var(--gray);font-weight:400;margin-left:.4rem;">
                                    ({{ count($group['variants']) }})
                                </span>
                            </div>
                            <button type="button" class="btn btn-outline btn-sm"
                                    onclick="addVariant({{ $gi }})">
                                <i class="fas fa-plus"></i> Ajouter
                            </button>
                        </div>

                        <div style="overflow-x:auto;">
                            <table style="width:100%;border-collapse:collapse;font-size:.82rem;" id="variants-table-{{ $gi }}">
                                <thead>
                                    <tr style="background:var(--light);">
                                        <th style="padding:.5rem .75rem;text-align:left;font-weight:600;border-bottom:1px solid var(--border);">Nom variante</th>
                                        <th style="padding:.5rem .75rem;text-align:left;font-weight:600;border-bottom:1px solid var(--border);">Couleur</th>
                                        <th style="padding:.5rem .75rem;text-align:left;font-weight:600;border-bottom:1px solid var(--border);">Taille</th>
                                        <th style="padding:.5rem .75rem;text-align:right;font-weight:600;border-bottom:1px solid var(--border);">Prix fournisseur</th>
                                        <th style="padding:.5rem .75rem;text-align:right;font-weight:600;border-bottom:1px solid var(--border);">Prix de vente</th>
                                        <th style="padding:.5rem .75rem;border-bottom:1px solid var(--border);"></th>
                                    </tr>
                                </thead>
                                <tbody id="variants-body-{{ $gi }}">
                                    @foreach($group['variants'] as $vi => $variant)
                                    @php
                                        $attrs       = $variant['attributes'] ?? [];
                                        $couleur     = $attrs['Couleur'] ?? '';
                                        $taille      = $attrs['Taille'] ?? '';
                                        $extraAttrs  = array_diff_key($attrs, array_flip(['Couleur','Taille']));
                                    @endphp
                                    <tr class="variant-row" id="vrow-{{ $gi }}-{{ $vi }}">
                                        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
                                            <input type="hidden" name="groups[{{ $gi }}][variants][{{ $vi }}][image_url]"
                                                   value="{{ $variant['image_url'] ?? '' }}">
                                            {{-- Conserver les attributs supplémentaires (Style, Matière…) --}}
                                            @foreach($extraAttrs as $attrKey => $attrVal)
                                            <input type="hidden" name="groups[{{ $gi }}][variants][{{ $vi }}][attributes][{{ $attrKey }}]"
                                                   value="{{ $attrVal }}">
                                            @endforeach
                                            <input type="text" name="groups[{{ $gi }}][variants][{{ $vi }}][name]"
                                                   class="form-input form-input-sm"
                                                   value="{{ old("groups.$gi.variants.$vi.name", $variant['name'] ?? '') }}"
                                                   placeholder="Ex: Rouge / M" required>
                                        </td>
                                        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
                                            <input type="text" name="groups[{{ $gi }}][variants][{{ $vi }}][attributes][Couleur]"
                                                   class="form-input form-input-sm"
                                                   value="{{ old("groups.$gi.variants.$vi.attributes.Couleur", $couleur) }}"
                                                   placeholder="Couleur">
                                        </td>
                                        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
                                            <input type="text" name="groups[{{ $gi }}][variants][{{ $vi }}][attributes][Taille]"
                                                   class="form-input form-input-sm"
                                                   value="{{ old("groups.$gi.variants.$vi.attributes.Taille", $taille) }}"
                                                   placeholder="Taille">
                                        </td>
                                        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
                                            <input type="number" name="groups[{{ $gi }}][variants][{{ $vi }}][supplier_price]"
                                                   class="form-input form-input-sm" style="text-align:right;"
                                                   value="{{ old("groups.$gi.variants.$vi.supplier_price", $variant['supplier_price'] ?? '') }}"
                                                   step="0.01" min="0" placeholder="0.00">
                                        </td>
                                        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
                                            <input type="number" name="groups[{{ $gi }}][variants][{{ $vi }}][price]"
                                                   class="form-input form-input-sm" style="text-align:right;"
                                                   value="{{ old("groups.$gi.variants.$vi.price", $variant['sale_price'] ?? '') }}"
                                                   step="0.01" min="0" placeholder="0.00" required>
                                        </td>
                                        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);text-align:center;">
                                            <button type="button" onclick="removeVariant(this)"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;padding:.2rem .4rem;border-radius:4px;"
                                                    title="Supprimer">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>{{-- /body --}}
        </div>{{-- /card --}}
        @endforeach

        {{-- Bouton bas --}}
        <div style="display:flex;justify-content:flex-end;gap:1rem;margin-top:.5rem;margin-bottom:2rem;">
            <a href="{{ route('admin.scraper.show', $filename) }}" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Créer {{ count($groups) }} produit(s)
            </button>
        </div>
    </form>
</div>

<style>
.input-warning { border-color: #f59e0b !important; background: #fffbeb !important; }
.form-input-sm { padding: .3rem .5rem; font-size: .82rem; }
</style>

<script>
// ─── Collapse / expand carte ──────────────────────────────────────
function toggleCard(gi) {
    const body    = document.getElementById('body-' + gi);
    const chevron = document.getElementById('chevron-' + gi);
    const isOpen  = body.style.display !== 'none';
    body.style.display    = isOpen ? 'none' : '';
    chevron.className     = isOpen ? 'fas fa-chevron-right' : 'fas fa-chevron-down';
}

// ─── Toggle simple / variable ─────────────────────────────────────
function onTypeChange(gi) {
    const radios  = document.querySelectorAll(`input[name="groups[${gi}][type]"]`);
    let   val     = 'simple';
    radios.forEach(r => { if (r.checked) val = r.value; });

    const section = document.getElementById('variants-section-' + gi);
    const badge   = document.querySelector('.type-badge-' + gi);
    const lblS    = document.getElementById('lbl-simple-'   + gi);
    const lblV    = document.getElementById('lbl-variable-' + gi);

    if (val === 'variable') {
        section.style.display = '';
        badge.textContent = 'Variable';
        badge.style.background = '#ede9fe'; badge.style.color = '#6d28d9';
        lblV.style.borderColor = 'var(--primary)'; lblV.style.background = '#eff6ff';
        lblS.style.borderColor = 'var(--border)';  lblS.style.background = '';
    } else {
        section.style.display = 'none';
        badge.textContent = 'Simple';
        badge.style.background = '#dbeafe'; badge.style.color = '#1d4ed8';
        lblS.style.borderColor = 'var(--primary)'; lblS.style.background = '#eff6ff';
        lblV.style.borderColor = 'var(--border)';  lblV.style.background = '';
    }
}

// Initialise les styles des radio buttons au chargement
document.addEventListener('DOMContentLoaded', function () {
    @foreach($groups as $gi => $group)
    onTypeChange({{ $gi }});
    @endforeach
});

// ─── Ajouter / supprimer variante ─────────────────────────────────
const variantCounters = {};

function addVariant(gi) {
    const tbody = document.getElementById('variants-body-' + gi);
    if (!variantCounters[gi]) {
        variantCounters[gi] = tbody.querySelectorAll('.variant-row').length;
    }
    const vi = variantCounters[gi]++;

    const tr = document.createElement('tr');
    tr.className = 'variant-row';
    tr.id = `vrow-${gi}-${vi}`;
    tr.innerHTML = `
        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
            <input type="hidden" name="groups[${gi}][variants][${vi}][image_url]" value="">
            <input type="text" name="groups[${gi}][variants][${vi}][name]"
                   class="form-input form-input-sm" placeholder="Ex: Rouge / M" required>
        </td>
        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
            <input type="text" name="groups[${gi}][variants][${vi}][attributes][Couleur]"
                   class="form-input form-input-sm" placeholder="Couleur">
        </td>
        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
            <input type="text" name="groups[${gi}][variants][${vi}][attributes][Taille]"
                   class="form-input form-input-sm" placeholder="Taille">
        </td>
        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
            <input type="number" name="groups[${gi}][variants][${vi}][supplier_price]"
                   class="form-input form-input-sm" style="text-align:right;" step="0.01" min="0" placeholder="0.00">
        </td>
        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);">
            <input type="number" name="groups[${gi}][variants][${vi}][price]"
                   class="form-input form-input-sm" style="text-align:right;" step="0.01" min="0" placeholder="0.00" required>
        </td>
        <td style="padding:.4rem .75rem;border-bottom:1px solid var(--border);text-align:center;">
            <button type="button" onclick="removeVariant(this)"
                    style="background:none;border:none;color:#dc2626;cursor:pointer;padding:.2rem .4rem;border-radius:4px;">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    updateVariantCount(gi);
}

function removeVariant(btn) {
    const row  = btn.closest('.variant-row');
    const gi   = row.id.split('-')[1];
    row.remove();
    updateVariantCount(gi);
}

function updateVariantCount(gi) {
    const count = document.getElementById('variants-body-' + gi).querySelectorAll('.variant-row').length;
    document.getElementById('variant-count-' + gi).textContent = `(${count})`;
}

// Spinner au submit
document.getElementById('reviewForm').addEventListener('submit', function () {
    const btn = this.querySelector('button[type="submit"]');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création en cours…';
    }
});
</script>
@endsection
