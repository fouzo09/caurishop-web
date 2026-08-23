<?php

use Database\Seeders\CitiesSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Référentiel des villes de livraison : le champ « ville » du carnet
     * d'adresses et du checkout devient un dropdown alimenté par cette table.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Données de référence : sans elles le sélecteur d'adresse est vide.
        (new CitiesSeeder)->run();
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
