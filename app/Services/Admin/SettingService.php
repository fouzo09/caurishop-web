<?php

namespace App\Services\Admin;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    private const CACHE_KEY = 'app_settings';

    public function all(): Collection
    {
        return Setting::orderBy('group')->orderBy('id')->get();
    }

    public function grouped(): array
    {
        $groups = [
            'general'       => ['label' => 'Général',        'icon' => 'fa-cog'],
            'credit'        => ['label' => 'Crédit',         'icon' => 'fa-hand-holding-usd'],
            'stock'         => ['label' => 'Stock',          'icon' => 'fa-boxes'],
            'notifications' => ['label' => 'Notifications',  'icon' => 'fa-bell'],
        ];

        $settings = $this->all()->groupBy('group');

        foreach ($groups as $key => &$meta) {
            $meta['settings'] = $settings->get($key, collect());
        }

        return $groups;
    }

    public function updateGroup(string $group, array $data): void
    {
        $settings = Setting::where('group', $group)->get();

        foreach ($settings as $setting) {
            if ($setting->type === 'boolean') {
                // checkbox non cochée = absent du POST → 0
                $value = isset($data[$setting->key]) ? '1' : '0';
            } else {
                $value = $data[$setting->key] ?? $setting->value;
            }

            $setting->update(['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::CACHE_KEY . '.' . $key, function () use ($key, $default) {
            return Setting::get($key, $default);
        });
    }
}
