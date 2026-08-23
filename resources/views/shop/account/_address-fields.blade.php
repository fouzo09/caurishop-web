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
    <label class="form-label">Ville</label>
    <select name="city_id" class="form-select" required>
      @foreach ($cities as $ville)
        <option value="{{ $ville->id }}" @selected(($a->city_id ?? null) === $ville->id)>{{ $ville->name }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Quartier</label>
    <input name="quartier" value="{{ $a->quartier ?? '' }}" class="form-control" placeholder="Ex. Almamya" required>
  </div>
  <div class="col-12">
    <label class="form-label">Précision <span class="text-muted" style="font-size:12px">(facultatif)</span></label>
    <input name="precision" value="{{ $a->precision ?? '' }}" class="form-control" placeholder="Ex. rue KA 020, en face de la pharmacie">
  </div>
  <div class="col-12">
    <label class="form-check d-flex align-items-center gap-2 m-0" style="font-size:13.5px">
      <input type="hidden" name="is_default" value="0">
      <input class="form-check-input m-0" type="checkbox" name="is_default" value="1" @checked($a->is_default ?? false)>
      Utiliser comme adresse de livraison par défaut
    </label>
  </div>
</div>
