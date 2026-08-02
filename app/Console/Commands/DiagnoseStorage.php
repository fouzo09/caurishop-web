<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Diagnostic du stockage des images produit.
 * À lancer sur le serveur quand les images ne s'affichent plus : php artisan storage:diagnose
 */
class DiagnoseStorage extends Command
{
    protected $signature = 'storage:diagnose';
    protected $description = 'Vérifie la chaîne de stockage et d\'affichage des images produit';

    public function handle(): int
    {
        $ok = true;

        $this->line('');
        $this->info('── Configuration ──');
        $this->line('  Disque par défaut  : ' . config('filesystems.default'));
        $this->line('  Racine « public »  : ' . config('filesystems.disks.public.root'));
        $this->line('  URL « public »     : ' . config('filesystems.disks.public.url'));
        $this->line('  APP_URL            : ' . config('app.url'));

        $this->line('');
        $this->info('── Lien symbolique ──');
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (! file_exists($link)) {
            $this->error("  ABSENT : {$link} n'existe pas.");
            $this->warn('  → Lancez : php artisan storage:link');
            $ok = false;
        } elseif (is_link($link)) {
            $resolved = readlink($link);
            $this->line("  Lien présent : {$link}");
            $this->line("  Pointe vers  : {$resolved}");

            if (realpath($resolved) !== realpath($target)) {
                $this->error('  Il ne pointe PAS vers ' . $target);
                $this->warn('  → Supprimez-le puis relancez : php artisan storage:link');
                $ok = false;
            }
        } else {
            $this->warn("  {$link} existe mais n'est pas un lien symbolique (dossier réel).");
            $this->warn('  Acceptable si votre hébergeur interdit les liens, à condition que le contenu y soit copié.');
        }

        $this->line('');
        $this->info('── Images en base ──');
        $total = ProductImage::count();
        $this->line("  {$total} image(s) référencée(s)");

        $missing = [];
        foreach (ProductImage::cursor() as $image) {
            if (! Storage::disk('public')->exists($image->path)) {
                $missing[] = $image->path;
            }
        }

        if ($missing) {
            $ok = false;
            $this->error('  ' . count($missing) . ' fichier(s) manquant(s) sur le disque :');
            foreach (array_slice($missing, 0, 10) as $path) {
                $this->line('    - ' . $path);
            }
            if (count($missing) > 10) {
                $this->line('    … et ' . (count($missing) - 10) . ' autre(s)');
            }
        } elseif ($total > 0) {
            $this->line('  Tous les fichiers sont présents.');
        }

        if ($total > 0) {
            $sample = ProductImage::first();
            $this->line('');
            $this->info('── Exemple ──');
            $this->line('  Chemin stocké : ' . $sample->path);
            $this->line('  URL générée   : ' . $sample->url);
            $this->line('  Fichier réel  : ' . Storage::disk('public')->path($sample->path));
            $this->line('  Testez cette URL dans un navigateur : ' . rtrim(config('app.url'), '/') . $sample->url);
        }

        $this->line('');
        $this->info('── Limites PHP d\'upload ──');
        foreach (['upload_max_filesize', 'post_max_size', 'max_file_uploads'] as $key) {
            $this->line('  ' . str_pad($key, 20) . ' : ' . ini_get($key));
        }

        $this->line('');

        if ($ok) {
            $this->info('Aucun problème détecté sur le stockage.');

            return self::SUCCESS;
        }

        $this->error('Des problèmes ont été détectés — voir ci-dessus.');

        return self::FAILURE;
    }
}
