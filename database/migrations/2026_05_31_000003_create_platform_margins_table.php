<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_margins', function (Blueprint $table) {
            $table->id();
            $table->enum('scope', ['global', 'product'])->default('global');
            $table->unsignedBigInteger('reference_id')->nullable(); // product_id quand scope=product
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 4); // ex: 10.5 pour 10.5%, ou 5000 pour 5000 GNF fixe
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['scope', 'is_active']);
            $table->index(['scope', 'reference_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_margins');
    }
};
