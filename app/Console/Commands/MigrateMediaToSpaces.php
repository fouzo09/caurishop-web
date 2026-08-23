<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\ProductImage;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Transfère les fichiers déjà stockés en local vers le disque média
 * (DigitalOcean Spaces) et réécrit les chemins en base.
 *
 *   php artisan media:migrate --dry-run   # simulation
 *   php artisan media:migrate             # transfert réel
 */
class MigrateMediaToSpaces extends Command
{
    protected $signature = 'media:migrate
                            {--from=public : Disque source des fichiers existants}
                            {--dry-run : Affiche ce qui serait transféré sans rien écrire}';

    protected $description = 'Transfère les images produit et documents d\'entreprise vers le disque média et met à jour les chemins';

    private bool $dry = false;

    public function handle(): int
    {
        $this->dry = (bool) $this->option('dry-run');
        $source    = Storage::disk($this->option('from'));
        $target    = Media::diskName();

        // Rien à reprendre quand le disque média est déjà le disque source :
        // on sort proprement pour ne pas interrompre un script de déploiement.
        if ($this->option('from') === $target) {
            $this->warn("Disque média identique au disque source ({$target}) — rien à transférer.");

            return self::SUCCESS;
        }

        $this->line('');
        $this->info("Source : {$this->option('from')}  →  Cible : {$target}" . ($this->dry ? '  [SIMULATION]' : ''));

        $images = $this->migrateProductImages($source);
        $docs   = $this->migrateCompanyDocs($source);

        $this->line('');
        $this->info(sprintf(
            '%s : %d image(s) et %d document(s) transférés.',
            $this->dry ? 'Simulation terminée' : 'Transfert terminé',
            $images,
            $docs,
        ));

        return self::SUCCESS;
    }

    /** Images produit → images/products/{id}/… */
    private function migrateProductImages($source): int
    {
        $this->line('');
        $this->info('── Images produit ──');

        $moved = 0;
        $bar   = $this->output->createProgressBar(ProductImage::count());
        $bar->start();

        foreach (ProductImage::cursor() as $image) {
            $bar->advance();

            $new = Media::productImages($image->product_id) . '/' . basename($image->path);

            if ($image->path === $new) {
                continue;   // déjà rangée au bon endroit
            }

            if ($this->copy($source, $image->path, $new)) {
                if (! $this->dry) {
                    $image->update(['path' => $new]);
                }
                $moved++;
            }
        }

        $bar->finish();
        $this->line('');

        return $moved;
    }

    /** Documents d'entreprise → rccm/{id}/…, nif/{id}/… */
    private function migrateCompanyDocs($source): int
    {
        $this->line('');
        $this->info('── Documents d\'entreprise ──');

        $fields = array_keys(Media::COMPANY_DOCS);
        $moved  = 0;

        $companies = Company::where(function ($q) use ($fields) {
            foreach ($fields as $field) {
                $q->orWhereNotNull($field);
            }
        })->get();

        foreach ($companies as $company) {
            $updates = [];

            foreach ($fields as $field) {
                if (! $company->$field) {
                    continue;
                }

                $new = Media::companyDoc($field, $company->id) . '/' . basename($company->$field);

                if ($company->$field === $new) {
                    continue;
                }

                if ($this->copy($source, $company->$field, $new)) {
                    $updates[$field] = $new;
                    $moved++;
                }
            }

            if ($updates && ! $this->dry) {
                $company->update($updates);
            }
        }

        $this->line('  ' . $companies->count() . ' entreprise(s) inspectée(s)');

        return $moved;
    }

    /** Copie un fichier du disque source vers le disque média. */
    private function copy($source, string $from, string $to): bool
    {
        if (! $source->exists($from)) {
            $this->line('');
            $this->warn("  Introuvable sur le disque source, ignoré : {$from}");

            return false;
        }

        if ($this->dry) {
            return true;
        }

        try {
            Media::disk()->writeStream($to, $source->readStream($from));

            return true;
        } catch (\Throwable $e) {
            $this->line('');
            $this->error("  Échec sur {$from} : " . $e->getMessage());

            return false;
        }
    }
}
