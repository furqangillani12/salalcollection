<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Richer shipping address (province / district / tehsil) and customer-submitted
 * bank-transfer proof for online orders. Additive — POS orders ignore these.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'shipping_province'))   $table->string('shipping_province')->nullable()->after('shipping_city');
            if (!Schema::hasColumn('orders', 'shipping_district'))   $table->string('shipping_district')->nullable()->after('shipping_province');
            if (!Schema::hasColumn('orders', 'shipping_tehsil'))     $table->string('shipping_tehsil')->nullable()->after('shipping_district');

            if (!Schema::hasColumn('orders', 'payment_proof_path'))    $table->string('payment_proof_path')->nullable()->after('online_payment_ref');
            if (!Schema::hasColumn('orders', 'payment_sender_name'))   $table->string('payment_sender_name')->nullable()->after('payment_proof_path');
            if (!Schema::hasColumn('orders', 'payment_sender_bank'))   $table->string('payment_sender_bank')->nullable()->after('payment_sender_name');
            if (!Schema::hasColumn('orders', 'payment_sender_amount')) $table->decimal('payment_sender_amount', 12, 2)->nullable()->after('payment_sender_bank');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['shipping_province', 'shipping_district', 'shipping_tehsil',
                      'payment_proof_path', 'payment_sender_name', 'payment_sender_bank', 'payment_sender_amount'] as $c) {
                if (Schema::hasColumn('orders', $c)) $table->dropColumn($c);
            }
        });
    }
};
