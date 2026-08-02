@extends('shop.layouts.app')

@section('title', 'Mes adresses — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'addresses'])</aside>

    <div class="col-lg-9">
      <div class="d-flex align-items-baseline justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="fw-bolder m-0" style="font-size:22px">Mes adresses</h1>
        <button type="button" class="btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#addressCreate">
          <i class="bi bi-plus-lg me-1"></i>Ajouter une adresse
        </button>
      </div>

      @if ($addresses->isEmpty())
        <div class="empty-state">
          <i class="bi bi-geo-alt"></i>
          Aucune adresse enregistrée. Ajoutez-en une pour aller plus vite au moment de commander.
          <div class="mt-3">
            <button type="button" class="btn-brand btn-sm" data-bs-toggle="modal" data-bs-target="#addressCreate">Ajouter une adresse</button>
          </div>
        </div>
      @else
        <div class="row row-cols-1 row-cols-md-2 g-3">
          @foreach ($addresses as $a)
            <div class="col">
              <div class="addr-card{{ $a->is_default ? ' addr-card--default' : '' }}">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                  <span class="addr-card__label">{{ $a->label ?: 'Adresse' }}</span>
                  @if ($a->is_default)<span class="addr-badge">Par défaut</span>@endif
                </div>

                <div class="addr-card__body">
                  {{ $a->full_name }}<br>
                  {{ $a->address }}<br>
                  {{ $a->city }} — Guinée<br>
                  {{ $a->phone }}
                </div>

                <div class="d-flex align-items-center gap-3 mt-3 flex-wrap" style="font-size:13px">
                  <button type="button" class="btn p-0 text-brand fw-semibold" data-bs-toggle="modal" data-bs-target="#addressEdit{{ $a->id }}">
                    <i class="bi bi-pencil me-1"></i>Modifier
                  </button>

                  @unless ($a->is_default)
                    <form method="POST" action="{{ route('shop.account.addresses.default', $a->id) }}">
                      @csrf @method('PUT')
                      <button type="submit" class="btn p-0 text-brand fw-semibold"><i class="bi bi-check2-circle me-1"></i>Définir par défaut</button>
                    </form>
                  @endunless

                  <form method="POST" action="{{ route('shop.account.addresses.destroy', $a->id) }}" class="ms-auto">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn p-0 fw-semibold" style="color:var(--danger)"><i class="bi bi-trash me-1"></i>Supprimer</button>
                  </form>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</main>

{{-- Modale : nouvelle adresse --}}
<div class="modal fade" id="addressCreate" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('shop.account.addresses.store') }}" class="modal-content" style="border-radius:10px">
      @csrf
      <div class="modal-header">
        <span class="fw-bolder" style="font-size:16px">Nouvelle adresse</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        @include('shop.account._address-fields', ['address' => null, 'cities' => $cities])
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-outline-ink btn-sm" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn-brand btn-sm">Enregistrer</button>
      </div>
    </form>
  </div>
</div>

{{-- Modales : édition --}}
@foreach ($addresses as $a)
  <div class="modal fade" id="addressEdit{{ $a->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form method="POST" action="{{ route('shop.account.addresses.update', $a->id) }}" class="modal-content" style="border-radius:10px">
        @csrf @method('PUT')
        <div class="modal-header">
          <span class="fw-bolder" style="font-size:16px">Modifier l'adresse</span>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          @include('shop.account._address-fields', ['address' => $a, 'cities' => $cities])
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-outline-ink btn-sm" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-brand btn-sm">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
@endforeach
@endsection
