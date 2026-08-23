<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Adresse de livraison structurée sur la commande.
     * `shipping_address` est conservée : elle garde la ligne d'adresse complète
     * (quartier — précision) pour les commandes déjà passées et les récapitulatifs.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_quartier')->nullable()->after('shipping_address');
            $table->string('shipping_precision')->nullable()->after('shipping_quartier');
        });

        // Les commandes existantes n'ont qu'une ligne libre : elle devient le quartier.
        DB::table('orders')->whereNotNull('shipping_address')
            ->update(['shipping_quartier' => DB::raw('shipping_address')]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_quartier', 'shipping_precision']);
        });
    }
};
