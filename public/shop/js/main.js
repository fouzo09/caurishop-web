/* ============================================================
   CAURISHOP — main.js
   Interactions communes du storefront. Bootstrap gère déjà :
   le carrousel du hero, les onglets produit, les onglets auth.
   ============================================================ */
(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    /* ---------- Carrousels horizontaux (flèches) ---------- */
    document.querySelectorAll("[data-hscroll]").forEach(function (wrap) {
      var row = wrap.querySelector(".hscroll");
      if (!row) return;
      wrap.querySelectorAll("[data-dir]").forEach(function (btn) {
        btn.addEventListener("click", function () {
          row.scrollBy({ left: btn.dataset.dir === "next" ? 496 : -496, behavior: "smooth" });
        });
      });
    });

    /* ---------- Compteurs de quantité (+ / −) ---------- */
    /* Fonctionne avec un <span class="val"> comme avec un <input class="val">. */
    document.querySelectorAll(".qty-box").forEach(function (box) {
      var val = box.querySelector(".val");
      if (!val) return;
      var isInput = val.tagName === "INPUT";
      var read = function () { return parseInt(isInput ? val.value : val.textContent, 10) || 1; };
      var write = function (n) {
        if (isInput) {
          val.value = n;
          // Permet au panier de se resoumettre tout seul (onchange sur l'input).
          val.dispatchEvent(new Event("change", { bubbles: true }));
        } else {
          val.textContent = n;
        }
      };

      var minus = box.querySelector("[data-minus]");
      var plus = box.querySelector("[data-plus]");
      if (minus) minus.addEventListener("click", function () { write(Math.max(1, read() - 1)); });
      if (plus) plus.addEventListener("click", function () { write(read() + 1); });
    });

    /* ---------- Galerie fiche produit ---------- */
    var mainImage = document.getElementById("mainImage");
    document.querySelectorAll(".thumb").forEach(function (thumb) {
      thumb.addEventListener("click", function () {
        document.querySelectorAll(".thumb").forEach(function (t) { t.classList.remove("active"); });
        thumb.classList.add("active");
        if (mainImage && thumb.dataset.full) mainImage.src = thumb.dataset.full;
      });
    });

    /* ---------- Options de paiement / livraison ---------- */
    /* Le clic sur la carte coche le radio qu'elle contient et met à jour l'état visuel. */
    document.querySelectorAll(".pay-option").forEach(function (option) {
      option.addEventListener("click", function () {
        var group = option.closest("[data-pay-group]") || option.parentElement;
        group.querySelectorAll(".pay-option").forEach(function (o) { o.classList.remove("selected"); });
        option.classList.add("selected");

        var radio = option.querySelector('input[type="radio"]');
        if (radio && !radio.checked) {
          radio.checked = true;
          radio.dispatchEvent(new Event("change", { bubbles: true }));
        }
      });
    });

    /* ---------- Favoris (bascule sans rechargement) ---------- */
    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.content : null;
    document.querySelectorAll("[data-fav-toggle]").forEach(function (btn) {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (!token) return;

        btn.disabled = true;
        fetch(btn.dataset.favToggle, {
          method: "POST",
          headers: { "X-CSRF-TOKEN": token, "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
        })
          .then(function (res) {
            if (res.status === 401 || res.status === 419) {
              window.location.href = btn.dataset.favLogin || "/connexion";
              return null;
            }
            return res.json();
          })
          .then(function (data) {
            if (!data) return;
            btn.classList.toggle("is-on", data.favorited);
            var icon = btn.querySelector("i");
            if (icon) icon.className = data.favorited ? "bi bi-heart-fill" : "bi bi-heart";
            btn.setAttribute("aria-pressed", data.favorited ? "true" : "false");

            // Sur la page « Mes favoris », le produit retiré disparaît de la liste.
            if (!data.favorited && btn.closest("[data-fav-page]")) {
              var cell = btn.closest(".col") || btn.closest(".pcard");
              if (cell) cell.remove();
            }
          })
          .finally(function () { btn.disabled = false; });
      });
    });
  });
})();
