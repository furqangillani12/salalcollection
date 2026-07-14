<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional remarks the operator types on the order page; they print in the
 * dispatch slip's Remarks box. Blank = the slip prints an empty box for
 * handwriting. Separate from the POS `notes` field so POS is untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'dispatch_remarks')) {
                $table->string('dispatch_remarks', 500)->nullable()->after('dispatch_cod_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'dispatch_remarks')) {
                $table->dropColumn('dispatch_remarks');
            }
        });
    }
};
