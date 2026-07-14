<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cart_items')) return;

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('session_id', 64)->nullable()->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->decimal('qty', 10, 2)->default(1);
            $table->decimal('unit_price', 12, 2);    // snapshot at the moment of add
            $table->string('selected_size', 50)->nullable();
            $table->string('selected_color', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
