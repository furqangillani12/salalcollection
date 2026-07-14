<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional reseller "From" address (printed as the sender on the dispatch slip
 * instead of the company), and an attached dispatch photo/short video. Additive.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'from_name'))           $table->string('from_name')->nullable()->after('shipping_post_code');
            if (!Schema::hasColumn('orders', 'from_phone'))          $table->string('from_phone')->nullable()->after('from_name');
            if (!Schema::hasColumn('orders', 'from_address'))        $table->string('from_address', 500)->nullable()->after('from_phone');
            if (!Schema::hasColumn('orders', 'dispatch_media_path')) $table->string('dispatch_media_path')->nullable()->after('from_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['from_name', 'from_phone', 'from_address', 'dispatch_media_path'] as $c) {
                if (Schema::hasColumn('orders', $c)) $table->dropColumn($c);
            }
        });
    }
};
