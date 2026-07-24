<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending_payment','pending_approval','draft','confirmed','completed','cancelled'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }
        DB::statement("ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check");
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK (status IN ('pending_approval','draft','confirmed','completed','cancelled'))");
    }
};
