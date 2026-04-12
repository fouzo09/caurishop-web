<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductImagesSeeder extends Seeder
{
    /**
     * Photos Unsplash par produit (IDs stables, libres de droits)
     */
    private array $catalog = [
        'Laptop Pro 14' => [
            'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=800&q=80&fit=crop', // laptop bureau
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=800&q=80&fit=crop', // MacBook argent
            'https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=800&q=80&fit=crop', // laptop noir
        ],
        'Routeur 4G' => [
            'https://images.unsplash.com/photo-1606904825846-647eb07f5be2?w=800&q=80&fit=crop', // routeur wifi
            'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80&fit=crop', // câbles réseau
        ],
        'T-Shirt CAURISHOP' => [
            'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=800&q=80&fit=crop', // t-shirt blanc
            'https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=800&q=80&fit=crop', // t-shirt porté
            'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=800&q=80&fit=crop', // t-shirts couleurs
        ],
        'Iphone 18' => [
            'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80&fit=crop', // iPhone face
            'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=800&q=80&fit=crop', // iPhone main
            'https://images.unsplash.com/photo-1580910051074-3eb694886505?w=800&q=80&fit=crop', // smartphone écran
        ],
    ];

    public function run(): void
    {
        foreach ($this->catalog as $productName => $urls) {
            $product = Product::where('name', $productName)->first();

            if (!$product) {
                $this->command->warn("  ✗  Produit introuvable : {$productName}");
                continue;
            }

            // Supprimer les anciennes images
            $product->images()->get()->each(function ($img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            });

            $this->command->line("  📦  {$productName}");

            foreach ($urls as $i => $url) {
                $path = $this->download($url, $product->id, $i);

                if (!$path) {
                    $this->command->warn("     ✗ Échec : {$url}");
                    continue;
                }

                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $path,
                    'sort_order' => $i,
                    'is_primary' => $i === 0,
                ]);

                $this->command->line("     ✓ Image " . ($i + 1));
            }
        }
    }

    private function download(string $url, int $productId, int $index): ?string
    {
        try {
            $response = Http::timeout(20)->withOptions(['allow_redirects' => true])->get($url);

            if (!$response->successful()) return null;

            $contentType = $response->header('Content-Type');
            $ext = str_contains($contentType, 'png') ? 'png' : 'jpg';

            $path = "products/{$productId}/img_{$index}.{$ext}";
            Storage::disk('public')->put($path, $response->body());

            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
