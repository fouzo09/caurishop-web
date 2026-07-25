@php
    $active = $active ?? 'index';
    $u = auth()->user();
    $c = $u->customer;
    $displayName = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: $u->name;
    $initials = strtoupper(mb_substr($c->first_name ?? $u->name, 0, 1) . mb_substr($c->last_name ?? '', 0, 1));
    $email = $c->email ?? $u->email;
@endphp
<div class="acct-side mb-3">
  <div class="acct-side__head">
    <span class="acct-avatar">{{ $initials ?: 'C' }}</span>
    <div class="overflow-hidden">
      <div class="acct-name text-truncate">{{ $displayName }}</div>
      <div class="acct-mail">{{ $email }}</div>
    </div>
  </div>
  <nav class="acct-nav">
    <a href="{{ route('shop.account.index') }}" class="acct-nav__link {{ $active === 'index' ? 'active' : '' }}">
      <i class="bi bi-grid-1x2"></i> Tableau de bord
    </a>
    <a href="{{ route('shop.account.orders') }}" class="acct-nav__link {{ $active === 'orders' ? 'active' : '' }}">
      <i class="bi bi-bag-check"></i> Mes commandes
    </a>
    <a href="{{ route('shop.account.profile') }}" class="acct-nav__link {{ $active === 'profile' ? 'active' : '' }}">
      <i class="bi bi-person-gear"></i> Profil &amp; adresse
    </a>
    <form method="POST" action="{{ route('shop.logout') }}">
      @csrf
      <button type="submit" class="acct-nav__link acct-nav__link--out">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
      </button>
    </form>
  </nav>
</div>
