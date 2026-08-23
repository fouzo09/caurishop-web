{{-- Messages de la boutique, tous en toast : rien ne bloque la page.
     Le détail des erreurs de validation reste sous chaque champ (@error). --}}
@php
    $flashSuccess = session('success');
    $flashError   = session('error');

    // Plusieurs erreurs : les messages inline font le détail, le toast résume.
    $validationError = $errors->any()
        ? ($errors->count() > 1 ? 'Veuillez corriger les champs signalés.' : $errors->first())
        : null;
@endphp

@if ($flashSuccess || $flashError || $validationError)
  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof Swal === 'undefined') return;

      var toast = function (icon, title, color, extra) {
        Swal.fire({
          toast: true, position: 'top-end', icon: icon, iconColor: color,
          title: title, showConfirmButton: false,
          timer: icon === 'error' ? 5000 : 3500, timerProgressBar: true,
          customClass: { popup: 'swal-cauri-toast' + (extra || '') },
        });
      };

      @if ($flashSuccess)
        toast('success', @json($flashSuccess), '#1F8A5B');
      @endif

      @if ($flashError)
        toast('error', @json($flashError), '#e0403a', ' swal-cauri-toast--error');
      @endif

      @if ($validationError)
        toast('error', @json($validationError), '#e0403a', ' swal-cauri-toast--error');
      @endif
    });
  </script>
  @endpush
@endif
