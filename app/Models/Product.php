<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    public const TYPE_SIMPLE = 'simple';
    public const TYPE_VARIABLE = 'variable';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'category_id',
        'description',
        'sku',
        'price',
        'supplier_price',
        'stock_quantity',
        'low_stock_threshold',
        'is_published',
        'is_active',
        'credit_enabled',
        'credit_duration_months',
        'credit_installments_count',
        'is_service',
        'provider',
        'supplier_id',
    ];

    protected $casts = [
        'price'                    => 'decimal:2',
        'supplier_price'           => 'decimal:2',
        'stock_quantity'           => 'integer',
        'low_stock_threshold'      => 'integer',
        'is_published'             => 'boolean',
        'is_active'                => 'boolean',
        'credit_enabled'           => 'boolean',
        'credit_duration_months'   => 'integer',
        'credit_installments_count'=> 'integer',
        'is_service'               => 'boolean',
    ];

    protected $appends = ['display_price', 'stock_status', 'display_monthly_payment'];

    // Relations
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /** Avis visibles publiquement (base des étoiles et du compteur). */
    public function approvedReviews()
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    // Scopes
    /**
     * Charge la note moyenne et le nombre d'avis en deux sous-requêtes :
     * évite un COUNT/AVG par produit dans les grilles et les cartes.
     */
    public function scopeWithRatings($query)
    {
        return $query->withAvg('approvedReviews as rating_avg', 'rating')
                     ->withCount('approvedReviews as rating_count');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('is_active', true);
    }

    public function scopeSimple($query)
    {
        return $query->where('type', self::TYPE_SIMPLE);
    }

    public function scopeVariable($query)
    {
        return $query->where('type', self::TYPE_VARIABLE);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($q2) {
                $q2->where('type', self::TYPE_SIMPLE)
                   ->where('stock_quantity', '>', 0);
            })->orWhere('type', self::TYPE_VARIABLE);
        });
    }

    public function isService(): bool
    {
        return (bool) $this->is_service;
    }

    // Type checks
    public function isSimple(): bool
    {
        return $this->type === self::TYPE_SIMPLE;
    }

    public function isVariable(): bool
    {
        return $this->type === self::TYPE_VARIABLE;
    }

    // Stock management
    public function hasStock(): bool
    {
        if ($this->isSimple()) {
            return $this->stock_quantity > 0;
        }

        return $this->variants()->where('is_active', true)->sum('stock_quantity') > 0;
    }

    public function totalStock(): int
    {
        if ($this->isSimple()) {
            return (int) $this->stock_quantity;
        }

        return (int) $this->variants()->where('is_active', true)->sum('stock_quantity');
    }

    public function isLowStock(): bool
    {
        if ($this->isVariable()) {
            return false;
        }

        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        if ($this->isVariable()) {
            return !$this->hasStock();
        }

        return $this->stock_quantity <= 0;
    }

    // Price & Credit
    public function displayPrice(): string
    {
        if ($this->isVariable()) {
            return 'Produit variant';
        }

        return number_format($this->price, 0, ',', ' ') . ' GNF';
    }

    public function monthlyPayment(): ?float
    {
        if (!$this->credit_enabled || !$this->credit_installments_count || $this->isVariable()) {
            return null;
        }

        return $this->price / $this->credit_installments_count;
    }

    public function displayMonthlyPayment(): ?string
    {
        $monthly = $this->monthlyPayment();
        return $monthly ? number_format($monthly, 0, ',', ' ') . ' GNF' : null;
    }

    // Accessors
    public function getDisplayPriceAttribute(): string
    {
        return $this->displayPrice();
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isService()) return 'disponible';

        if ($this->isVariable()) {
            return $this->hasStock() ? 'disponible' : 'rupture';
        }

        if ($this->isOutOfStock()) return 'rupture';
        if ($this->isLowStock())   return 'faible';
        return 'disponible';
    }

    public function getDisplayMonthlyPaymentAttribute(): ?string
    {
        return $this->displayMonthlyPayment();
    }

    public function coverUrl(): ?string
    {
        $primary = $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();

        return $primary?->url;
    }

    /**
     * Axes de variation déduits des attributs des variantes actives.
     * Ex. couleur => [Argent, Gris Sidéral], stockage => [256 Go, 512 Go].
     *
     * Chaque valeur porte le visuel de la première variante qui l'utilise.
     * Un axe ne passe en vignettes que si *toutes* ses valeurs sont illustrées :
     * sinon on retombe sur des pastilles de texte, pour ne pas mélanger une
     * photo et des libellés dans la même ligne.
     *
     * @return array<int, array{key: string, label: string, imaged: bool, values: array<int, array{value: string, image: ?string}>}>
     */
    public function variantAxes(): array
    {
        $axes = [];

        foreach ($this->variants->where('is_active', true) as $variant) {
            $cover = $variant->coverUrl();

            foreach ($variant->options() as $key => $value) {
                if ($value === '') {
                    continue;
                }

                // Première image rencontrée pour cette valeur, conservée ensuite.
                $axes[$key][$value] = $axes[$key][$value] ?? $cover;
            }
        }

        return array_map(
            fn ($key, $values) => [
                'key'    => $key,
                'label'  => Str::ucfirst(str_replace('_', ' ', $key)),
                'imaged' => count(array_filter($values)) === count($values),
                'values' => array_map(
                    fn ($value, $image) => ['value' => $value, 'image' => $image],
                    array_keys($values),
                    $values,
                ),
            ],
            array_keys($axes),
            $axes,
        );
    }

    /**
     * Variantes sérialisées pour le sélecteur de la fiche produit : le script
     * retrouve la variante correspondant à la combinaison choisie.
     *
     * @return array<int, array<string, mixed>>
     */
    public function variantPayload(): array
    {
        return $this->variants->where('is_active', true)->map(fn ($variant) => [
            'id'      => $variant->id,
            'attrs'   => $variant->options(),
            'price'   => Money::gnf($variant->price),
            'inStock' => $variant->hasStock(),
            'image'   => $variant->coverUrl(),
            'label'   => $variant->name,
        ])->values()->all();
    }

    /**
     * Note moyenne sur 5 (0 si aucun avis).
     * Utilise la valeur préchargée par scopeWithRatings() quand elle existe.
     */
    public function ratingAverage(): float
    {
        // array_key_exists : scopeWithRatings() renvoie null quand il n'y a
        // aucun avis, on ne doit pas repartir en base pour autant.
        $avg = array_key_exists('rating_avg', $this->attributes)
            ? $this->attributes['rating_avg']
            : $this->approvedReviews()->avg('rating');

        return round((float) $avg, 1);
    }

    /** Nombre d'avis publiés. */
    public function ratingCount(): int
    {
        return (int) (array_key_exists('rating_count', $this->attributes)
            ? $this->attributes['rating_count']
            : $this->approvedReviews()->count());
    }
}
