<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndAdminSeeder::class,
            PermissionsSeeder::class,
            SettingsSeeder::class,
            DemoDataSeeder::class,
            TestAccountsSeeder::class,
            ShopCategoriesSeeder::class,
            CitiesSeeder::class,
        ]);
    }
}
