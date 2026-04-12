<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('credit_plans', function (Blueprint $table) {
            // Montant de l'acompte versé à la commande.
            // outstanding_amount = total_amount - down_payment_amount
            $table->decimal('down_payment_amount', 14, 2)->default(0)->after('total_amount');
        });
    }

    public function down(): void
    {
        Schema::table('credit_plans', function (Blueprint $table) {
            $table->dropColumn('down_payment_amount');
        });
    }
};
