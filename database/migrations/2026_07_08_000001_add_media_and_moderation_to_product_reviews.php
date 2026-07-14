<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Customer-uploaded photos / short videos for the review.
            if (! Schema::hasColumn('product_reviews', 'media')) {
                $table->json('media')->nullable()->after('body');
            }
            // Guards the one-time reward-point award when a review is approved.
            if (! Schema::hasColumn('product_reviews', 'points_awarded')) {
                $table->boolean('points_awarded')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'media')) {
                $table->dropColumn('media');
            }
            if (Schema::hasColumn('product_reviews', 'points_awarded')) {
                $table->dropColumn('points_awarded');
            }
        });
    }
};
