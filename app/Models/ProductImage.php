<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;

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

    /** URL publique de l'image, servie depuis le disque média (DigitalOcean Spaces). */
    public function getUrlAttribute(): string
    {
        return (string) Media::url($this->path);
    }
}
