@php $cat = $category ?? null; @endphp
<div style="margin-bottom:1rem;">
    <label class="form-label">Nom <span style="color:#dc2626;">*</span></label>
    <input type="text" name="name" class="form-input" value="{{ old('name', $cat->name ?? '') }}" required autofocus>
</div>

<div style="margin-bottom:1rem;">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-input" rows="2" placeholder="Courte description (facultatif)">{{ old('description', $cat->description ?? '') }}</textarea>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
    <div>
        <label class="form-label">Icône <span style="color:var(--gray);font-weight:400;">(facultatif)</span></label>
        <input type="text" name="icon" class="form-input" value="{{ old('icon', $cat->icon ?? '') }}" placeholder="Ex : bi-tag ou fa-mobile">
    </div>
    <div>
        <label class="form-label">Ordre d'affichage</label>
        <input type="number" name="sort_order" class="form-input" min="0" value="{{ old('sort_order', $cat->sort_order ?? 0) }}">
    </div>
</div>

<div style="margin-bottom:1.5rem;">
    <label style="display:inline-flex;align-items:center;gap:.5rem;cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $cat->is_active ?? true) ? 'checked' : '' }}>
        <span>Catégorie active (visible dans la boutique)</span>
    </label>
</div>
