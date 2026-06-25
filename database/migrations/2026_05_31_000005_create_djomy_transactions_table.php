<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('djomy_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('djomy_transaction_id')->nullable()->index();
            $table->string('merchant_reference')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending'); // pending|success|failed|cancelled
            $table->string('payment_method')->nullable();
            $table->string('payer_identifier')->nullable();
            $table->json('djomy_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('djomy_transactions');
    }
};
