<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editable "COD amount to collect" for the dispatch slip. NULL = not overridden,
 * so the slip falls back to (paid ? 0 : balance). Lets the operator set exactly
 * what the courier should collect when the auto value can't be trusted
 * (e.g. EasyPaisa proof submitted but not yet verified).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'dispatch_cod_amount')) {
                $table->decimal('dispatch_cod_amount', 10, 2)->nullable()->after('dispatch_media_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'dispatch_cod_amount')) {
                $table->dropColumn('dispatch_cod_amount');
            }
        });
    }
};
