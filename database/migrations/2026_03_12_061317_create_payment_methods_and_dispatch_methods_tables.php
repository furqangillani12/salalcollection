<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payment_methods')) {
            Schema::create('payment_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');          // stored value e.g. "cash"
                $table->string('label');         // display e.g. "Cash"
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dispatch_methods')) {
            Schema::create('dispatch_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');          // e.g. "Self Pickup"
                $table->boolean('has_tracking')->default(false);
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_methods');
        Schema::dropIfExists('payment_methods');
    }
};
