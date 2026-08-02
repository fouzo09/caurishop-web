<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Réassocie les photos de database/seeders/fouzimport-images aux produits
 * FOUZIMPORT déjà en base, sans les recréer.
 *
 * Utile quand la migration a déjà tourné avec les visuels générés : les vraies
 * photos arrivées ensuite viennent simplement les remplacer. Appariement par SKU.
 */
class SyncFouzimportImages extends Command
{
    protected $signature = 'fouzimport:images
                            {--dry-run : Affiche ce qui serait fait sans rien modifier}';

    protected $description = 'Associe les photos livrées avec le dépôt aux produits FOUZIMPORT existants';

    public function handle(): int
    {
        $supplier = Supplier::where('name', 'FOUZIMPORT')->first();

        if (! $supplier) {
            $this->error('Fournisseur FOUZIMPORT introuvable. Lancez d\'abord php artisan migrate.');

            return self::FAILURE;
        }

        $source = database_path('seeders/fouzimport-images');

        if (! is_dir($source)) {
            $this->error("Dossier de photos introuvable : {$source}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $products = Product::where('supplier_id', $supplier->id)->with('images')->orderBy('sku')->get();

        $updated = $skipped = $photos = 0;

        foreach ($products as $product) {
            $available = [];

            foreach ([0, 1] as $position) {
                $file = "{$source}/{$product->sku}-{$position}.jpg";

                if (is_file($file)) {
                    $available[$position] = $file;
                }
            }

            if (! $available) {
                $skipped++;
                continue;
            }

            $updated++;
            $photos += count($available);

            if ($dryRun) {
                $this->line(sprintf('  %s  %-45s %d photo(s)', $product->sku, mb_strimwidth($product->name, 0, 44, '…'), count($available)));
                continue;
            }

            // On efface les visuels actuels du produit, fichiers compris.
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }

            foreach ($available as $position => $file) {
                $path = "products/{$product->id}/{$product->sku}-{$position}.jpg";
                Storage::disk('public')->put($path, file_get_contents($file));

                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $path,
                    'sort_order' => $position,
                    'is_primary' => $position === array_key_first($available),
                ]);
            }
        }

        $this->line('');
        $this->info($dryRun ? 'Simulation — rien n\'a été modifié.' : 'Photos associées.');
        $this->line("  Produits mis à jour : {$updated}");
        $this->line("  Photos installées   : {$photos}");
        $this->line("  Sans photo fournie  : {$skipped} (visuel généré conservé)");
        $this->line('');

        return self::SUCCESS;
    }
}
