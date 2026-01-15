<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_plans', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('order_id')->unique()->constrained('orders')->cascadeOnDelete();

            $table->unsignedInteger('duration_months');
            $table->unsignedInteger('installments_count');

            $table->decimal('total_amount', 14, 2);
            $table->decimal('outstanding_amount', 14, 2);

            $table->enum('status', ['active', 'closed'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_plans');
    }
};
