<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Avis clients : une note de 1 à 5 accompagnée d'un commentaire.
     * Un seul avis par client et par produit (modifiable ensuite).
     */
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');          // 1 à 5
            $table->string('title', 120)->nullable();
            $table->text('comment');
            $table->boolean('is_verified')->default(false); // le client a déjà commandé ce produit
            $table->boolean('is_approved')->default(true);  // modération éventuelle

            $table->timestamps();

            $table->unique(['product_id', 'customer_id']);
            $table->index(['product_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
