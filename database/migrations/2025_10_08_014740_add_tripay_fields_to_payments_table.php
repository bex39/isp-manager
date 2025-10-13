<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('tripay_reference')->nullable()->after('payment_method');
            $table->string('tripay_merchant_ref')->nullable()->after('tripay_reference');
            $table->string('checkout_url')->nullable()->after('tripay_merchant_ref');
            $table->string('qr_url')->nullable()->after('checkout_url');
            $table->timestamp('expired_at')->nullable()->after('qr_url');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'tripay_reference',
                'tripay_merchant_ref',
                'checkout_url',
                'qr_url',
                'expired_at'
            ]);
        });
    }
};
