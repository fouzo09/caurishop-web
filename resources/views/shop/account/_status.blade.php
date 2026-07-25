@php
    $map = [
        'draft'             => ['pending',   'bi-hourglass-split', 'Brouillon'],
        'pending_payment'   => ['pending',   'bi-clock-history',   'En attente de paiement'],
        'pending_approval'  => ['pending',   'bi-hourglass-split', 'En attente'],
        'confirmed'         => ['confirmed', 'bi-check-circle',    'Confirmée'],
        'completed'         => ['completed', 'bi-check-circle-fill','Livrée'],
        'cancelled'         => ['cancelled', 'bi-x-circle',        'Annulée'],
    ];
    $s = $map[$status] ?? ['confirmed', 'bi-circle', ucfirst(str_replace('_', ' ', (string) $status))];
@endphp
<span class="st-pill st-pill--{{ $s[0] }}"><i class="bi {{ $s[1] }}"></i> {{ $s[2] }}</span>
