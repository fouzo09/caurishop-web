<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();

            $table->enum('order_type', ['cash', 'credit']);
            $table->enum('status', ['draft', 'confirmed', 'completed', 'cancelled'])->default('draft');

            $table->decimal('total_amount', 14, 2)->default(0);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'order_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
