<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('delivery_charge_slabs')) {
            return;
        }

        Schema::create('delivery_charge_slabs', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_weight', 8, 3); // in kg (0.5 = 500g)
            $table->decimal('max_weight', 8, 3); // in kg
            $table->decimal('charge', 10, 2);     // delivery charge in Rs.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_charge_slabs');
    }
};
