<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('credit_plan_id')->constrained('credit_plans')->cascadeOnDelete();

            $table->unsignedInteger('installment_number');
            $table->date('due_date');

            $table->decimal('amount_due', 14, 2);
            $table->decimal('amount_paid', 14, 2)->default(0);

            $table->enum('status', ['pending', 'partial', 'paid', 'late'])->default('pending');

            $table->timestamps();

            $table->unique(['credit_plan_id', 'installment_number']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
