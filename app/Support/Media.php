<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Point d'entrée unique du stockage des fichiers utilisateurs.
 *
 * Tout passe par le disque par défaut (`FILESYSTEM_DISK`), DigitalOcean Spaces
 * en production, et les fichiers sont rangés par type à la racine de l'espace :
 * images produits sous `images/`, documents d'entreprise sous `rccm/`, `nif/`…
 */
class Media
{
    /** Dossier racine de chaque document d'entreprise. */
    public const COMPANY_DOCS = [
        'doc_rccm'     => 'rccm',
        'doc_nif'      => 'nif',
        'doc_statuts'  => 'statuts',
        'doc_cni'      => 'cni',
        'doc_patente'  => 'patente',
        'doc_domicile' => 'domicile',
    ];

    /** Nom du disque utilisé pour les fichiers utilisateurs. */
    public static function diskName(): string
    {
        return config('filesystems.default');
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(self::diskName());
    }

    /** Dossier des images d'un produit : `images/products/{id}`. */
    public static function productImages(int $productId): string
    {
        return 'images/products/' . $productId;
    }

    /** Dossier d'un document d'entreprise : `rccm/{id}`, `nif/{id}`… */
    public static function companyDoc(string $field, int $companyId): ?string
    {
        $folder = self::COMPANY_DOCS[$field] ?? null;

        return $folder ? $folder . '/' . $companyId : null;
    }

    /** URL publique d'un fichier stocké, null si aucun chemin. */
    public static function url(?string $path): ?string
    {
        return $path ? self::disk()->url($path) : null;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            self::disk()->delete($path);
        }
    }
}
