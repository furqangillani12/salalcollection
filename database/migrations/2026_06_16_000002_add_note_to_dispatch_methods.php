<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatch_methods', function (Blueprint $table) {
            // Customer-facing note shown at checkout when this method is picked
            // (e.g. "Pakistan Post — estimated delivery 5–7 working days").
            $table->string('note', 500)->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('dispatch_methods', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
