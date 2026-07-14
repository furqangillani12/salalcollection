<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'logo')) {
                $table->string('logo')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('branches', 'order_start_number')) {
                $table->unsignedInteger('order_start_number')->nullable()->after('logo');
            }
        });
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('branches', 'logo'))                $cols[] = 'logo';
            if (Schema::hasColumn('branches', 'order_start_number'))  $cols[] = 'order_start_number';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
