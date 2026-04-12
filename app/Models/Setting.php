<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type', 'label', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => (bool) $setting->value,
            'number'  => is_numeric($setting->value) ? (float) $setting->value : $default,
            default   => $setting->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
    }
}
