<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\Shop\CartService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('fr');

        // Formatage des prix en francs guinéens : @gnf($montant) => "1 250 000 GNF"
        Blade::directive('gnf', fn ($expression) => "<?php echo \\App\\Support\\Money::gnf($expression); ?>");

        // Données partagées par les partials du parcours public (header/footer).
        View::composer(['shop.partials.header', 'shop.partials.footer'], function ($view) {
            $view->with('shopCategories', Category::active()->orderBy('sort_order')->orderBy('name')->get());
        });

        View::composer('shop.partials.header', function ($view) {
            $view->with('shopCartCount', app(CartService::class)->lineCount());
        });

        // Favoris du client connecté — résolus une seule fois par requête,
        // la carte produit étant incluse des dizaines de fois par page.
        View::composer('shop.partials.product-card', function ($view) {
            static $favoriteIds = null;

            if ($favoriteIds === null) {
                $customer = auth()->check() ? auth()->user()->customer : null;
                $favoriteIds = $customer ? $customer->favorites()->pluck('product_id')->all() : [];
            }

            $view->with('shopFavoriteIds', $favoriteIds);
        });
    }
}
