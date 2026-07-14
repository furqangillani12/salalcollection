<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersTableWithDefaults extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(0)->change();
            $table->decimal('tax', 10, 2)->default(0)->change();
            $table->decimal('discount', 10, 2)->default(0)->change();
            $table->decimal('total', 10, 2)->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 10, 2)->default(null)->change();
            $table->decimal('tax', 10, 2)->default(null)->change();
            $table->decimal('discount', 10, 2)->default(null)->change();
            $table->decimal('total', 10, 2)->default(null)->change();
        });
    }
}
