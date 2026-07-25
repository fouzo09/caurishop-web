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
          title: @json(session('success')),
          showConfirmButton: false, timer: 3500, timerProgressBar: true,
        });
      @endif

      @if (session('error'))
        Swal.fire({
          toast: true, position: 'top-end', icon: 'error',
          title: @json(session('error')),
          showConfirmButton: false, timer: 4000, timerProgressBar: true,
        });
      @endif

      @if ($errors->any())
        Swal.fire({
          icon: 'error',
          title: 'Une erreur est survenue',
          html: @json($errHtml),
          confirmButtonText: 'OK',
          confirmButtonColor: '#1E4FD6',
        });
      @endif
    });
  </script>
  @endpush
@endif
