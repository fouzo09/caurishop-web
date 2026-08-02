<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            $table->string('label', 60)->nullable();      // « Domicile », « Bureau »…
            $table->string('full_name', 120);
            $table->string('phone', 30);
            $table->string('city', 100);                  // préfecture / région
            $table->string('address', 255);               // quartier, rue, repère
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index(['customer_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
