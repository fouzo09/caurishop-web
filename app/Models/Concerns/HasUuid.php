<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot the trait.
     */
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {

            if (empty($model->{$model->getKeyName()})) {

                // Laravel 10+ supporte uuid7()
                $model->{$model->getKeyName()} = method_exists(Str::class, 'uuid7')
                    ? (string) Str::uuid7()
                    : (string) Str::uuid();
            }
        });
    }

    /**
     * Désactive l'auto-increment.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * Clé primaire de type string.
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
