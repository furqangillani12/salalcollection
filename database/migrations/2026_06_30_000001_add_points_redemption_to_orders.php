<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Points redemption on storefront orders (#B). Online-only fields; the POS
 * never sets them, so they default to 0 and leave POS orders untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'points_redeemed')) {
                $table->unsignedInteger('points_redeemed')->default(0)->after('coupon_discount');
            }
            if (!Schema::hasColumn('orders', 'points_discount')) {
                $table->decimal('points_discount', 10, 2)->default(0)->after('points_redeemed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['points_redeemed', 'points_discount'] as $col) {
                if (Schema::hasColumn('orders', $col)) $table->dropColumn($col);
            }
        });
    }
};
