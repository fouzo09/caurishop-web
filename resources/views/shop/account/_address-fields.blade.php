{{-- Champs communs aux modales « nouvelle adresse » et « modifier l'adresse ». --}}
@php $a = $address ?? null; @endphp
<div class="row g-3">
  <div class="col-12">
    <label class="form-label">Libellé <span class="text-muted" style="font-size:12px">(facultatif)</span></label>
    <input name="label" value="{{ $a->label ?? '' }}" class="form-control" placeholder="Domicile, bureau…">
  </div>
  <div class="col-md-6">
    <label class="form-label">Nom complet</label>
    <input name="full_name" value="{{ $a->full_name ?? '' }}" class="form-control" placeholder="Aïssatou Diallo" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Téléphone</label>
    <input name="phone" value="{{ $a->phone ?? '' }}" class="form-control" placeholder="+224 6XX XX XX XX" required>
  </div>
  <div class="col-md-6">
    <label class="form-label">Préfecture / région</label>
    <select name="city" class="form-select" required>
      @foreach ($cities as $ville)
        <option value="{{ $ville }}" @selected(($a->city ?? 'Conakry') === $ville)>{{ $ville }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-12">
    <label class="form-label">Quartier / repère</label>
    <input name="address" value="{{ $a->address ?? '' }}" class="form-control" placeholder="Ex. Almamya, rue KA 020, en face de la pharmacie" required>
  </div>
  <div class="col-12">
    <label class="form-check d-flex align-items-center gap-2 m-0" style="font-size:13.5px">
      <input type="hidden" name="is_default" value="0">
      <input class="form-check-input m-0" type="checkbox" name="is_default" value="1" @checked($a->is_default ?? false)>
      Utiliser comme adresse de livraison par défaut
    </label>
  </div>
</div>
