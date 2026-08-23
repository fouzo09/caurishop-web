<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Référentiel des villes de livraison (idempotent : rejouable sans doublon).
 */
class CitiesSeeder extends Seeder
{
    /** Villes proposées, dans l'ordre du dropdown. */
    public const CITIES = [
        'Conakry',
        'Kindia',
        'Boffa',
        'Boké',
        'Labé',
        'Mamou',
        'Faranah',
        'Kankan',
        "N'Zérékoré",
    ];

    public function run(): void
    {
        foreach (self::CITIES as $index => $name) {
            City::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
