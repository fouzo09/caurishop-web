<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['simple', 'variable']); // simple ou avec variantes
            $table->string('name');
            $table->text('description')->nullable();

            // Si produit simple: price / sku ici
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 14, 2)->nullable();

            $table->boolean('is_published')->default(false);
            $table->boolean('is_active')->default(true);

            // CREDIT CONFIG (par défaut au niveau produit)
            $table->boolean('credit_enabled')->default(false);
            $table->unsignedInteger('credit_duration_months')->nullable();
            $table->unsignedInteger('credit_installments_count')->nullable();

            $table->timestamps();

            $table->index(['type', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
