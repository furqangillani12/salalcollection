<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'dispatch_method')) {
                $table->string('dispatch_method')->nullable();
            }
            if (!Schema::hasColumn('orders', 'tracking_id')) {
                $table->string('tracking_id')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('orders', 'dispatch_method')) $cols[] = 'dispatch_method';
            if (Schema::hasColumn('orders', 'tracking_id'))     $cols[] = 'tracking_id';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }

};
