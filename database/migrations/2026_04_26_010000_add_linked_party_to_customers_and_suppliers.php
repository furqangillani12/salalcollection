<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Some customers are also suppliers (we sell to them and buy from them).
 * Add nullable, indexed cross-references on both tables so a single click
 * can offset their A/R against our A/P. Idempotent.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('customers') && !Schema::hasColumn('customers', 'linked_supplier_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedBigInteger('linked_supplier_id')->nullable()->after('credit_enabled');
                $table->index('linked_supplier_id');
            });
        }

        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'linked_customer_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->unsignedBigInteger('linked_customer_id')->nullable()->after('company_name');
                $table->index('linked_customer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'linked_supplier_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropIndex(['linked_supplier_id']);
                $table->dropColumn('linked_supplier_id');
            });
        }
        if (Schema::hasColumn('suppliers', 'linked_customer_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropIndex(['linked_customer_id']);
                $table->dropColumn('linked_customer_id');
            });
        }
    }
};
