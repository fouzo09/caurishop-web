@extends('shop.layouts.app')

@section('title', 'Mes favoris — CAURISHOP')

@section('content')
<main class="container-xl py-4">
  <div class="row g-4 align-items-start">
    <aside class="col-lg-3">@include('shop.account._nav', ['active' => 'favorites'])</aside>

    <div class="col-lg-9">
      <div class="d-flex align-items-baseline justify-content-between mb-3 flex-wrap gap-2">
        <h1 class="fw-bolder m-0" style="font-size:22px">Mes favoris</h1>
        @if ($products->isNotEmpty())
          <span class="text-muted" style="font-size:13.5px">{{ $products->count() }} produit{{ $products->count() > 1 ? 's' : '' }}</span>
        @endif
      </div>

      @if ($products->isEmpty())
        <div class="empty-state">
          <i class="bi bi-heart"></i>
          Vous n'avez pas encore de favoris. Touchez le cœur sur un produit pour le retrouver ici.
          <div class="mt-3"><a href="{{ route('shop.products.index') }}" class="btn-brand btn-sm">Découvrir la boutique</a></div>
        </div>
      @else
        <div class="row row-cols-2 row-cols-md-3 g-3" data-fav-page>
          @foreach ($products as $product)
            <div class="col">@include('shop.partials.product-card', ['product' => $product])</div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</main>
@endsection
