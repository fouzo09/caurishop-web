<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'path', 'sort_order', 'is_primary'];

    protected $casts = [
        'is_primary'  => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Les fichiers sont écrits sur le disque « public » (storage/app/public).
     * On nomme ce disque explicitement : Storage::url() viserait le disque par
     * défaut (local, racine storage/app/private), où le fichier n'existe pas.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
