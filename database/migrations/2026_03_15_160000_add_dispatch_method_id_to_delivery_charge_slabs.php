<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Only truncate + add column on a fresh DB that doesn't yet have the column.
        // If the column already exists, the schema is already at target state and
        // we must NOT wipe the existing slab data.
        if (!Schema::hasColumn('delivery_charge_slabs', 'dispatch_method_id')) {
            DB::table('delivery_charge_slabs')->truncate();

            Schema::table('delivery_charge_slabs', function (Blueprint $table) {
                $table->foreignId('dispatch_method_id')->after('id')->constrained('dispatch_methods')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::table('delivery_charge_slabs', function (Blueprint $table) {
            $table->dropForeign(['dispatch_method_id']);
            $table->dropColumn('dispatch_method_id');
        });
    }
};
