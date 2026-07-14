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
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'resale_price')) {
                $table->decimal('resale_price', 10, 2)->nullable()->after('sale_price');
            }
            if (!Schema::hasColumn('products', 'wholesale_price')) {
                $table->decimal('wholesale_price', 10, 2)->nullable()->after('resale_price');
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer_type')) {
                $table->enum('customer_type', ['customer', 'reseller', 'wholesale'])
                    ->default('customer')
                    ->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sale_price', 'resale_price', 'wholesale_price']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('customer_type');
        });
    }
};
