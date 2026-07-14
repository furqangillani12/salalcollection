<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The orders.status column was a narrow ENUM
 *   ('pending','completed','cancelled','refunded')
 * which crashed when we tried to set 'confirmed', 'shipped' or 'delivered'
 * for online orders. Widen to VARCHAR(30) — keeps all existing POS values
 * unchanged, allows the storefront flow to write its own values.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'status')) return;

        // Use raw SQL — doctrine/dbal can choke on enum→varchar conversion.
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `status` VARCHAR(30) NOT NULL DEFAULT 'completed'");
    }

    public function down(): void
    {
        // Don't try to recreate the original enum on rollback — VARCHAR is
        // strictly more permissive, so leaving it widened is harmless.
    }
};
