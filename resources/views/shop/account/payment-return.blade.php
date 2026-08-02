@extends('shop.layouts.app')

@section('title', 'Vérification du paiement — CAURISHOP')

@section('content')
@php
    $fallback = $txn->installment_id
        ? route('shop.account.payments')
        : route('shop.account.orders.show', $txn->order_id);
@endphp

<main class="container-xl py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-xl-5">
      <div class="border rounded-3 p-5 text-center">

        {{-- Vérification en cours --}}
        <div id="state-loading">
          <div class="spinner-border text-brand mb-3" style="width:48px;height:48px" role="status"><span class="visually-hidden">Chargement…</span></div>
          <h1 class="fw-bolder m-0" style="font-size:20px">Vérification du paiement…</h1>
          <p class="text-muted mt-2 mb-0" style="font-size:14px">Ne fermez pas cette page, cela prend quelques secondes.</p>
        </div>

        {{-- Succès --}}
        <div id="state-success" class="d-none">
          <span class="ok-badge">✓</span>
          <h1 class="fw-bolder m-0" style="font-size:20px">Paiement confirmé</h1>
          <p class="text-muted mt-2 mb-0" style="font-size:14px" id="msg-success">Merci, votre paiement a bien été enregistré.</p>
          <p class="text-muted mt-3 mb-0" style="font-size:12.5px">Redirection en cours…</p>
        </div>

        {{-- Échec --}}
        <div id="state-failed" class="d-none">
          <span class="rounded-circle d-grid mx-auto mb-3" style="width:72px;height:72px;background:var(--danger-soft);color:var(--danger);place-items:center;font-size:34px">
            <i class="bi bi-x-lg"></i>
          </span>
          <h1 class="fw-bolder m-0" style="font-size:20px">Paiement non abouti</h1>
          <p class="text-muted mt-2 mb-0" style="font-size:14px" id="msg-failed">Le paiement a échoué ou a été annulé.</p>
          <a href="{{ $fallback }}" class="btn-brand mt-4 d-inline-block">Réessayer</a>
        </div>

        {{-- Toujours en attente --}}
        <div id="state-pending" class="d-none">
          <span class="rounded-circle d-grid mx-auto mb-3" style="width:72px;height:72px;background:var(--amber-soft);color:var(--amber);place-items:center;font-size:32px">
            <i class="bi bi-hourglass-split"></i>
          </span>
          <h1 class="fw-bolder m-0" style="font-size:20px">Paiement en cours de traitement</h1>
          <p class="text-muted mt-2 mb-0" style="font-size:14px">
            Votre opérateur n'a pas encore confirmé. Le statut se mettra à jour automatiquement dès réception.
          </p>
          <a href="{{ $fallback }}" class="btn-outline-ink mt-4 d-inline-block" id="link-pending">Continuer</a>
        </div>

        <div class="text-muted mt-4" style="font-size:11.5px">Référence : {{ $ref }}</div>
      </div>
    </div>
  </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
  var checkUrl = @json(route('portal.djomy.check-status', ['ref' => $ref]));
  var fallback = @json($fallback);
  var tries = 0, maxTries = 12;

  function show(state) {
    ['loading', 'success', 'failed', 'pending'].forEach(function (s) {
      document.getElementById('state-' + s).classList.toggle('d-none', s !== state);
    });
  }

  function check() {
    tries++;
    fetch(checkUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.status === 'success') {
          if (data.message) document.getElementById('msg-success').textContent = data.message;
          show('success');
          setTimeout(function () { window.location.href = data.redirect || fallback; }, 2000);
        } else if (data.status === 'failed') {
          if (data.message) document.getElementById('msg-failed').textContent = data.message;
          show('failed');
        } else if (tries < maxTries) {
          setTimeout(check, 2500);
        } else {
          show('pending');
        }
      })
      .catch(function () {
        if (tries < maxTries) { setTimeout(check, 2500); } else { show('pending'); }
      });
  }

  setTimeout(check, 1500);
})();
</script>
@endpush
