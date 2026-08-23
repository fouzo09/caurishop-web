<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            // URL relative : les images restent valides quel que soit le domaine
            // servi, même si APP_URL est mal renseigné en production.
            'url' => '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        /*
         | DigitalOcean Spaces — stockage et diffusion de tous les fichiers
         | utilisateurs (images produits, documents d'entreprise).
         | Compatible S3 : même driver, endpoint et URL propres à DO.
         */
        'spaces' => [
            'driver'                  => 's3',
            'key'                     => env('DO_SPACES_KEY'),
            'secret'                  => env('DO_SPACES_SECRET'),
            'region'                  => env('DO_SPACES_REGION', 'sfo3'),
            'bucket'                  => env('DO_SPACES_BUCKET', 'caurishop'),
            // Endpoint d'API (sans le bucket) et URL publique (avec le bucket).
            'endpoint'                => env('DO_SPACES_ENDPOINT', 'https://sfo3.digitaloceanspaces.com'),
            'url'                     => env('DO_SPACES_URL', 'https://caurishop.sfo3.digitaloceanspaces.com'),
            'use_path_style_endpoint' => false,
            // Les objets déposés doivent être lisibles directement depuis l'URL.
            'visibility'              => 'public',
            'throw'                   => false,
            'report'                  => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
