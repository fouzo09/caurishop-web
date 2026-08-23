{{-- Messages de succès / d'erreur de l'admin, affichés via SweetAlert.
     Inclus une seule fois par le gabarit : les vues n'ont plus de bloc .alert.
     Les messages `@error` sous chaque champ restent en place, eux désignent
     précisément le champ fautif. --}}
@php
    $flashSuccess = session('success') ?: session('password_success');
    $flashError   = session('error');

    // Plusieurs erreurs : les messages inline font le détail, le toast résume.
    $validationError = $errors->any()
        ? ($errors->count() > 1 ? 'Veuillez corriger les champs signalés.' : $errors->first())
        : null;
@endphp

@if ($flashSuccess || $flashError || $validationError)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swal === 'undefined') return;

        var toast = function (icon, title, color, extra) {
            Swal.fire({
                toast: true, position: 'top-end', icon: icon, iconColor: color,
                title: title, showConfirmButton: false,
                timer: icon === 'error' ? 5000 : 3500, timerProgressBar: true,
                customClass: { popup: 'swal-admin-toast' + (extra || '') },
            });
        };

        @if ($flashSuccess)
            toast('success', @json($flashSuccess), '#16a34a');
        @endif

        @if ($flashError)
            toast('error', @json($flashError), '#dc2626', ' swal-admin-toast--error');
        @endif

        @if ($validationError)
            toast('error', @json($validationError), '#dc2626', ' swal-admin-toast--error');
        @endif
    });
</script>
@endif
