@if (session('success') || session('error') || $errors->any())
  @php
    $errHtml = '';
    if ($errors->any()) {
        $errHtml = '<ul style="text-align:left;margin:0;padding-left:1.15em">';
        foreach ($errors->all() as $e) {
            $errHtml .= '<li>' . e($e) . '</li>';
        }
        $errHtml .= '</ul>';
    }
  @endphp
  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof Swal === 'undefined') return;

      @if (session('success'))
        Swal.fire({
          toast: true, position: 'top-end', icon: 'success',
          iconColor: '#1F8A5B',
          title: @json(session('success')),
          showConfirmButton: false, timer: 3500, timerProgressBar: true,
          customClass: { popup: 'swal-cauri-toast' },
        });
      @endif

      @if (session('error'))
        Swal.fire({
          toast: true, position: 'top-end', icon: 'error',
          iconColor: '#e0403a',
          title: @json(session('error')),
          showConfirmButton: false, timer: 4000, timerProgressBar: true,
          customClass: { popup: 'swal-cauri-toast swal-cauri-toast--error' },
        });
      @endif

      @if ($errors->any())
        Swal.fire({
          icon: 'error',
          iconColor: '#e0403a',
          title: 'Oups, une erreur est survenue',
          html: @json($errHtml),
          confirmButtonText: "J'ai compris",
          buttonsStyling: false,
          customClass: {
            popup: 'swal-cauri',
            title: 'swal-cauri__title',
            htmlContainer: 'swal-cauri__html',
            actions: 'swal-cauri__actions',
            confirmButton: 'swal-cauri__btn',
          },
        });
      @endif
    });
  </script>
  @endpush
@endif
