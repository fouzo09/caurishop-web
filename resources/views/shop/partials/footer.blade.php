<footer class="site-footer border-top mt-5">
  <div class="border-bottom">
    <div class="container-xl py-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div>
        <div class="fw-bold fs-5 text-ink">Restez informé des bons plans</div>
        <div class="text-muted small mt-1">Recevez nos offres et nouveautés en avant-première.</div>
      </div>
      <div class="d-flex gap-2 flex-nowrap">
        <input class="form-control newsletter-input" placeholder="Votre adresse e-mail" aria-label="E-mail newsletter">
        <button class="btn btn-brand px-4 flex-shrink-0" type="button">S'abonner</button>
      </div>
    </div>
  </div>

  <div class="container-xl py-5">
    <div class="row g-4">
      <div class="col-12 col-md-6 col-lg-4">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 mb-3">
          <span class="logo__mark logo__mark--sm">C</span>
          <span class="logo__name fs-5">CAURISHOP</span>
        </a>
        <p class="text-muted small lh-lg mb-3" style="max-width:320px;">Le marché en ligne de la Guinée. Livraison à Conakry et dans toutes les régions.</p>
        <div class="d-flex flex-column gap-2 text-muted small">
          <span>📍 Kaloum, Conakry — Guinée</span>
          <span>📞 +224 620 00 00 00</span>
          <span>✉️ bonjour@caurishop.gn</span>
        </div>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="footer-title">Boutique</div>
        @foreach ($shopCategories->take(5) as $cat)
          <a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}" class="footer-link">{{ $cat->name }}</a>
        @endforeach
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <div class="footer-title">Aide</div>
        <a href="{{ route('shop.contact') }}" class="footer-link">Suivi de commande</a>
        <a href="{{ route('shop.contact') }}" class="footer-link">Livraison &amp; délais</a>
        <a href="{{ route('shop.contact') }}" class="footer-link">Retours</a>
        <a href="{{ route('shop.contact') }}" class="footer-link">Nous contacter</a>
        <a href="{{ route('shop.contact') }}" class="footer-link">FAQ</a>
      </div>
      <div class="col-12 col-lg-4">
        <div class="footer-title">Paiement</div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
          <span class="pay-badge" title="Orange Money"><img src="{{ asset('shop/img/pay/orange.svg') }}" alt="Orange Money" height="20"></span>
          <span class="pay-badge pay-badge--mtn" title="MTN MoMo">MTN</span>
          <span class="pay-badge" title="Visa"><img src="{{ asset('shop/img/pay/visa.svg') }}" alt="Visa" height="13"></span>
          <span class="pay-badge" title="Mastercard"><img src="{{ asset('shop/img/pay/mastercard.svg') }}" alt="Mastercard" height="22"></span>
          <span class="pay-badge pay-badge--text" title="Paycard">Paycard</span>
        </div>
        <div class="footer-title mt-4">Suivez-nous</div>
        <div class="d-flex gap-2">
          <a href="#" class="social" title="Facebook" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social" title="Instagram" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social" title="TikTok" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
    </div>
  </div>

  <div class="border-top">
    <div class="container-xl py-3 d-flex align-items-center justify-content-between flex-wrap gap-2 footer-bottom">
      <span>© {{ date('Y') }} CAURISHOP — Tous droits réservés.</span>
      <span class="d-flex gap-3">
        <a href="{{ route('shop.contact') }}">Conditions</a>
        <a href="{{ route('shop.contact') }}">Confidentialité</a>
        <a href="{{ route('shop.contact') }}">Retours</a>
      </span>
    </div>
  </div>
</footer>
