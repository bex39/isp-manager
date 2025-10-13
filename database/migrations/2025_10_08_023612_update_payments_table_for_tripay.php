<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Add columns if not exist
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'tripay_reference')) {
                $table->string('tripay_reference')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'tripay_merchant_ref')) {
                $table->string('tripay_merchant_ref')->nullable()->after('tripay_reference');
            }
            if (!Schema::hasColumn('payments', 'checkout_url')) {
                $table->string('checkout_url')->nullable()->after('tripay_merchant_ref');
            }
            if (!Schema::hasColumn('payments', 'qr_url')) {
                $table->string('qr_url')->nullable()->after('checkout_url');
            }
            if (!Schema::hasColumn('payments', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('qr_url');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('expired_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'tripay_reference',
                'tripay_merchant_ref',
                'checkout_url',
                'qr_url',
                'expired_at',
                'paid_at'
            ]);
        });
    }
};
