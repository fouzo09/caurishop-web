@php $active = $active ?? 'index'; @endphp
<div class="side-card mb-3">
  <div class="side-card__title">Mon compte</div>
  <a href="{{ route('shop.account.index') }}" class="footer-link d-block {{ $active === 'index' ? 'fw-bold text-brand' : '' }}">🏠 Tableau de bord</a>
  <a href="{{ route('shop.account.orders') }}" class="footer-link d-block {{ $active === 'orders' ? 'fw-bold text-brand' : '' }}">📦 Mes commandes</a>
  <a href="{{ route('shop.account.profile') }}" class="footer-link d-block {{ $active === 'profile' ? 'fw-bold text-brand' : '' }}">👤 Mon profil &amp; adresse</a>
  <form method="POST" action="{{ route('shop.logout') }}" class="mt-2">
    @csrf
    <button type="submit" class="btn btn-link btn-sm text-muted p-0">↪ Se déconnecter</button>
  </form>
</div>
