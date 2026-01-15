<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['individual', 'company']); // particulier ou entreprise
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_contact_name')->nullable(); // pour entreprise

            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('address')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['type', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
