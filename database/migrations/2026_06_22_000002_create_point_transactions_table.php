<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Reward-points ledger (#22). Customer balance lives on customers.loyalty_points. */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('point_transactions')) return;

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->integer('points'); // signed: + earned, - redeemed
            $table->string('type')->default('adjust'); // earn_order, earn_review, review_photo, review_video, review_social, adjust, redeem
            $table->string('note')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // admin who granted (if manual)
            $table->timestamps();
            $table->index(['customer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
