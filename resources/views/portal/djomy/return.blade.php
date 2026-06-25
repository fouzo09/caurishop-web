@extends('tpl.portal')

@section('content')
<div class="page-content" style="max-width:400px;margin:3rem auto;">
    <div class="card">
        <div class="card-body" style="padding:2.5rem;text-align:center;">

            {{-- Loader --}}
            <div id="state-loading">
                <div style="width:48px;height:48px;border:3px solid #dbeafe;border-top-color:#2563eb;border-radius:50%;animation:spin .75s linear infinite;margin:0 auto 1.5rem;"></div>
                <p style="color:var(--gray);font-size:.95rem;">Vérification du paiement en cours…</p>
            </div>

            {{-- Succès --}}
            <div id="state-success" style="display:none;">
                <div style="width:48px;height:48px;background:#dbeafe;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                    <i class="fas fa-check" style="color:#2563eb;font-size:1.2rem;"></i>
                </div>
                <h3 style="font-size:1rem;color:#1e293b;margin-bottom:.4rem;">Paiement confirmé</h3>
                <p id="msg-success" style="font-size:.875rem;color:var(--gray);margin-bottom:.75rem;"></p>
                <span style="font-size:.8rem;color:var(--gray);">Redirection en cours…</span>
            </div>

            {{-- Échec --}}
            <div id="state-failed" style="display:none;">
                <div style="width:48px;height:48px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                    <i class="fas fa-times" style="color:#dc2626;font-size:1.2rem;"></i>
                </div>
                <h3 style="font-size:1rem;color:#1e293b;margin-bottom:.4rem;">Paiement échoué</h3>
                <p id="msg-failed" style="font-size:.875rem;color:var(--gray);margin-bottom:.75rem;"></p>
                <span style="font-size:.8rem;color:var(--gray);">Redirection en cours…</span>
            </div>

            {{-- En attente --}}
            <div id="state-pending" style="display:none;">
                <div style="width:48px;height:48px;background:#fef3c7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;">
                    <i class="fas fa-clock" style="color:#d97706;font-size:1.2rem;"></i>
                </div>
                <h3 style="font-size:1rem;color:#1e293b;margin-bottom:.4rem;">Paiement en attente</h3>
                <p style="font-size:.875rem;color:var(--gray);margin-bottom:1.25rem;">Le statut sera mis à jour automatiquement.</p>
                <a id="link-pending" href="#" class="btn btn-primary btn-sm">Retourner à ma commande</a>
            </div>

        </div>
    </div>
</div>

<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

<script>
(function () {
    const checkUrl = @json(route('portal.djomy.check-status', ['ref' => $ref]));
    const fallback = @json($txn->installment_id ? route('portal.payments.index') : ($txn->order_id ? route('portal.orders.show', $txn->order_id) : route('portal.orders.index')));
    const maxTries = 8;
    let tries = 0;

    function show(id) {
        ['loading','success','failed','pending'].forEach(s =>
            document.getElementById('state-' + s).style.display = s === id ? '' : 'none'
        );
    }

    function check() {
        tries++;
        fetch(checkUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('msg-success').textContent = data.message;
                    show('success');
                    setTimeout(() => window.location.href = data.redirect, 2000);
                } else if (data.status === 'failed') {
                    document.getElementById('msg-failed').textContent = data.message;
                    show('failed');
                    setTimeout(() => window.location.href = data.redirect, 3000);
                } else if (tries < maxTries) {
                    setTimeout(check, 2500);
                } else {
                    document.getElementById('link-pending').href = fallback;
                    show('pending');
                }
            })
            .catch(() => tries < maxTries ? setTimeout(check, 2500) : (document.getElementById('link-pending').href = fallback, show('pending')));
    }

    setTimeout(check, 1500);
})();
</script>
@endsection
