<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) return;

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_source')) {
                $table->enum('order_source', ['pos', 'online', 'phone'])->default('pos')->after('order_type')->index();
            }
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('orders', 'shipping_first_name')) {
                $table->string('shipping_first_name')->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipping_last_name')) {
                $table->string('shipping_last_name')->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipping_phone')) {
                $table->string('shipping_phone', 30)->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipping_address1')) {
                $table->string('shipping_address1')->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipping_address2')) {
                $table->string('shipping_address2')->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipping_city')) {
                $table->string('shipping_city')->nullable();
            }
            if (!Schema::hasColumn('orders', 'shipping_country')) {
                $table->string('shipping_country', 100)->default('Pakistan');
            }
            if (!Schema::hasColumn('orders', 'shipping_post_code')) {
                $table->string('shipping_post_code', 20)->nullable();
            }
            if (!Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code', 50)->nullable();
            }
            if (!Schema::hasColumn('orders', 'coupon_discount')) {
                $table->decimal('coupon_discount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'online_payment_status')) {
                // 'cod', 'bank_pending', 'bank_paid', 'jazzcash_pending', 'jazzcash_paid', 'cancelled'
                $table->string('online_payment_status', 30)->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'online_payment_ref')) {
                $table->string('online_payment_ref')->nullable();
            }
            if (!Schema::hasColumn('orders', 'order_notes_customer')) {
                $table->text('order_notes_customer')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('orders')) return;
        Schema::table('orders', function (Blueprint $table) {
            $cols = ['order_notes_customer','online_payment_ref','online_payment_status','coupon_discount','coupon_code',
                    'shipping_post_code','shipping_country','shipping_city','shipping_address2','shipping_address1',
                    'shipping_phone','shipping_last_name','shipping_first_name','customer_email','order_source'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
