<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_status');
            // Frais de livraison et remise du parcours public (n'altèrent pas total_amount).
            $table->decimal('delivery_fee', 14, 2)->nullable()->after('payment_reference');
            $table->decimal('discount_amount', 14, 2)->nullable()->after('delivery_fee');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'delivery_fee', 'discount_amount']);
        });
    }
};
