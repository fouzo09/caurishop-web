@php
    $active = $active ?? 'index';
    $u = auth()->user();
    $c = $u->customer;
    $displayName = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: $u->name;
    $initials = strtoupper(mb_substr($c->first_name ?? $u->name, 0, 1) . mb_substr($c->last_name ?? '', 0, 1));
    $memberSince = $c?->created_at ?? $u->created_at;

    // Client rattaché à une entreprise : accès au crédit et à ses échéances.
    $isCompanyCustomer = $c?->company_id !== null;
    $isCompanyAdmin    = $u->isCompanyAdmin();

    $links = [
        'index'     => [route('shop.account.index'),     'bi-grid',        'Tableau de bord'],
        'orders'    => [route('shop.account.orders'),    'bi-box-seam',    'Mes commandes'],
        'addresses' => [route('shop.account.addresses'), 'bi-geo-alt',     'Mes adresses'],
        'favorites' => [route('shop.account.favorites'), 'bi-heart',       'Mes favoris'],
        'profile'   => [route('shop.account.profile'),   'bi-person-gear', 'Mon profil'],
    ];

    $creditLinks = $isCompanyCustomer ? [
        'payments' => [route('shop.account.payments'), 'bi-wallet2', 'Mes échéances'],
    ] : [];

    $companyLinks = $isCompanyAdmin ? [
        'company'        => [route('shop.account.company.index'),  'bi-building', 'Mon entreprise'],
        'company.orders' => [route('shop.account.company.orders'), 'bi-receipt',  'Commandes entreprise'],
        'company.staff'  => [route('shop.account.company.staff'),  'bi-people',   'Salariés'],
    ] : [];
@endphp
<div class="acct-side">
  <div class="acct-side__head">
    <span class="acct-avatar">{{ $initials ?: 'C' }}</span>
    <div class="overflow-hidden">
      <div class="acct-name text-truncate">{{ $displayName }}</div>
      <div class="acct-mail">{{ $c?->company?->name ?? 'Client depuis ' . $memberSince?->translatedFormat('F Y') }}</div>
    </div>
  </div>

  @foreach ($links as $key => $link)
    <a href="{{ $link[0] }}" class="filter-item{{ $active === $key ? ' active' : '' }}">
      <i class="bi {{ $link[1] }}"></i>{{ $link[2] }}
    </a>
  @endforeach

  @if ($creditLinks)
    <div class="acct-side__title">Crédit</div>
    @foreach ($creditLinks as $key => $link)
      <a href="{{ $link[0] }}" class="filter-item{{ $active === $key ? ' active' : '' }}">
        <i class="bi {{ $link[1] }}"></i>{{ $link[2] }}
      </a>
    @endforeach
  @endif

  @if ($companyLinks)
    <div class="acct-side__title">Entreprise</div>
    @foreach ($companyLinks as $key => $link)
      <a href="{{ $link[0] }}" class="filter-item{{ $active === $key ? ' active' : '' }}">
        <i class="bi {{ $link[1] }}"></i>{{ $link[2] }}
      </a>
    @endforeach
  @endif

  <form method="POST" action="{{ route('shop.logout') }}">
    @csrf
    <button type="submit" class="filter-item filter-item--out mb-0">
      <i class="bi bi-box-arrow-right"></i>Déconnexion
    </button>
  </form>
</div>
