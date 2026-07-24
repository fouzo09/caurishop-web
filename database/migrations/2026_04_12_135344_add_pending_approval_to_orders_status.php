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
        // PostgreSQL : on remplace le CHECK constraint sur status (no-op sur les autres SGBD, ex. SQLite en test)
        if (\DB::getDriverName() !== 'pgsql') {
            return;
        }
        \DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        \DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending_approval','draft','confirmed','completed','cancelled'))");
    }

    public function down(): void
    {
        if (\DB::getDriverName() !== 'pgsql') {
            return;
        }
        \DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        \DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('draft','confirmed','completed','cancelled'))");
    }
};
