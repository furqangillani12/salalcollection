<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('resale_price', 10, 2)->nullable()->after('sale_price');
            $table->decimal('wholesale_price', 10, 2)->nullable()->after('resale_price');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['resale_price', 'wholesale_price']);
        });
    }
};
