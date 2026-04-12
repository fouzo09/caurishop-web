<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Termes du crédit saisis à la création (brouillon).
            // Utilisés pour générer le credit_plan à la confirmation.
            $table->unsignedInteger('credit_installments_count')->nullable()->after('down_payment');
            $table->unsignedInteger('credit_duration_months')->nullable()->after('credit_installments_count');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['credit_installments_count', 'credit_duration_months']);
        });
    }
};
