<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 60);           // order_created, payment_received, installment_late, ...
            $table->string('title');
            $table->text('message');
            $table->string('icon', 60)->default('fa-bell');
            $table->string('color', 20)->default('primary'); // primary, success, warning, danger
            $table->string('link')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
    }
};
