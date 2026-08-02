<div class="newsletter-strip">
  <div class="container-xl d-flex align-items-center justify-content-between flex-wrap gap-3 py-4 px-3">
    <div><div class="fw-bolder" style="font-size:19px">Restez informé des bons plans</div><div class="text-muted" style="font-size:13.5px">Recevez nos offres et nouveautés en avant-première.</div></div>
    <div class="d-flex gap-2"><input class="form-control" style="min-width:280px" placeholder="Votre adresse e-mail" aria-label="E-mail newsletter"><button class="btn-brand text-nowrap" type="button">S'abonner</button></div>
  </div>
</div>
<footer class="site-footer">
  <div class="container-xl"><div class="row g-4 py-4 mx-0">
    <div class="col-12 col-md-6 col-lg-4">
      <a href="{{ route('home') }}" class="logo d-block mb-2" style="font-size:17px">caurishop<span class="text-brand">.</span></a>
      <p style="max-width:280px;line-height:1.6">Le marché en ligne de la Guinée. Livraison à Conakry et dans toutes les régions.</p>
      <div class="d-flex flex-column gap-2">
        <span><i class="bi bi-geo-alt me-2"></i>Kaloum, Conakry — Guinée</span>
        <span><i class="bi bi-telephone me-2"></i>+224 620 00 00 00</span>
      </div>
    </div>
    <div class="col-6 col-md-3 col-lg-2"><div class="col-title mb-3">Boutique</div>@foreach ($shopCategories->take(4) as $cat)<a href="{{ route('shop.products.index', ['category' => $cat->slug]) }}">{{ $cat->name }}</a>@endforeach</div>
    <div class="col-6 col-md-3 col-lg-2"><div class="col-title mb-3">Aide</div><a href="{{ route('shop.contact') }}">Suivi de commande</a><a href="{{ route('shop.contact') }}">Livraison &amp; délais</a><a href="{{ route('shop.contact') }}">Retours</a><a href="{{ route('shop.contact') }}">Nous contacter</a></div>
    <div class="col-12 col-lg-4"><div class="col-title mb-3">Paiement</div><div class="d-flex flex-wrap gap-2"><span class="pay-chip">Orange Money</span><span class="pay-chip">MTN MoMo</span><span class="pay-chip">Visa</span></div></div>
  </div></div>
  <div class="footer-bottom"><div class="container-xl d-flex align-items-center justify-content-between flex-wrap gap-2 py-3 px-3">
    <span>© {{ date('Y') }} CAURISHOP — Tous droits réservés.</span>
    <span class="d-flex gap-3"><a href="{{ route('shop.contact') }}">Conditions</a><a href="{{ route('shop.contact') }}">Confidentialité</a></span>
  </div></div>
</footer>
