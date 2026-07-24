/* ============================================================
   CAURISHOP — main.js
   Interactions communes du template. Bootstrap gère déjà :
   le carrousel du hero, les onglets produit, les pills du compte.
   ============================================================ */
(function () {
  "use strict";

  var fmt = function (n) {
    return n.toLocaleString("fr-FR").replace(/\u202f/g, " ") + " GNF";
  };

  /* ---------- Compteurs de quantité (produit + panier) ---------- */
  document.querySelectorAll(".qty-stepper").forEach(function (stepper) {
    var value = stepper.querySelector(".qty-value");
    stepper.querySelectorAll("button[data-step]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var q = Math.max(1, parseInt(value.textContent, 10) + parseInt(btn.dataset.step, 10));
        value.textContent = q;
        recomputeCart();
      });
    });
  });

  /* ---------- Panier : totaux dynamiques ---------- */
  function recomputeCart() {
    var rows = document.querySelectorAll(".cart-row");
    if (!rows.length) return;

    var subtotal = 0;
    rows.forEach(function (row) {
      var price = parseInt(row.dataset.price, 10);
      var qty = parseInt(row.querySelector(".qty-value").textContent, 10);
      var line = price * qty;
      subtotal += line;
      row.querySelector(".cart-line-total").textContent = fmt(line);
    });

    var discount = Math.round(subtotal * 0.05); // code KARITE25 : -5%
    var elSub = document.getElementById("cart-subtotal");
    var elDisc = document.getElementById("cart-discount");
    var elTot = document.getElementById("cart-total");
    if (elSub) elSub.textContent = fmt(subtotal);
    if (elDisc) elDisc.textContent = fmt(discount);
    if (elTot) elTot.textContent = fmt(subtotal - discount);
  }

  document.querySelectorAll(".cart-remove").forEach(function (btn) {
    btn.addEventListener("click", function () {
      btn.closest(".cart-row").remove();
      recomputeCart();
    });
  });

  recomputeCart();

  /* ---------- Boutique : libellé du filtre de prix ---------- */
  var range = document.getElementById("priceRange");
  var priceLabel = document.getElementById("priceLabel");
  if (range && priceLabel) {
    range.addEventListener("input", function () {
      priceLabel.textContent = fmt(parseInt(range.value, 10));
    });
  }

  /* ---------- Produit : galerie de vignettes ---------- */
  var mainEmoji = document.getElementById("mainEmoji");
  if (mainEmoji) {
    document.querySelectorAll(".thumb").forEach(function (thumb) {
      thumb.addEventListener("click", function () {
        document.querySelectorAll(".thumb").forEach(function (t) {
          t.classList.remove("thumb--active");
        });
        thumb.classList.add("thumb--active");
        mainEmoji.textContent = thumb.dataset.emoji;
        var mainImg = document.getElementById("mainImg");
        if (mainImg && thumb.dataset.img) {
          mainImg.src = thumb.dataset.img.replace("w=300&h=300", "w=900&h=900");
        }
      });
    });
  }

  /* ---------- Produit : sélection motif / taille ---------- */
  document.querySelectorAll(".swatch").forEach(function (btn) {
    btn.addEventListener("click", function () {
      document.querySelectorAll(".swatch").forEach(function (b) {
        b.classList.remove("swatch--active");
      });
      btn.classList.add("swatch--active");
    });
  });
  document.querySelectorAll(".size-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
      document.querySelectorAll(".size-btn").forEach(function (b) {
        b.classList.remove("size-btn--active");
      });
      btn.classList.add("size-btn--active");
    });
  });

  /* ---------- Checkout : livraison + moyen de paiement ---------- */
  var CK_SUBTOTAL = 1850000;
  var CK_DISCOUNT = Math.round(CK_SUBTOTAL * 0.05);

  function updateCheckout() {
    var chosen = document.querySelector('input[name="delivery"]:checked');
    var elDeliv = document.getElementById("ck-delivery");
    var elTotal = document.getElementById("ck-total");
    if (!chosen || !elTotal) return;
    var cost = parseInt(chosen.dataset.cost, 10);
    elDeliv.textContent = cost === 0 ? "Offerte" : fmt(cost);
    elTotal.textContent = fmt(CK_SUBTOTAL + cost - CK_DISCOUNT);
  }
  document.querySelectorAll('input[name="delivery"]').forEach(function (radio) {
    radio.addEventListener("change", updateCheckout);
  });
  updateCheckout();

  function updatePayment() {
    var chosen = document.querySelector('input[name="payment"]:checked');
    var hint = document.getElementById("payHintText");
    var phone = document.getElementById("payPhone");
    var card = document.getElementById("payCard");
    if (!chosen || !hint) return;
    hint.textContent = chosen.dataset.hint;
    if (phone) phone.classList.toggle("d-none", chosen.dataset.show !== "phone");
    if (card) card.classList.toggle("d-none", chosen.dataset.show !== "card");
  }
  document.querySelectorAll('input[name="payment"]').forEach(function (radio) {
    radio.addEventListener("change", updatePayment);
  });
  updatePayment();
})();
