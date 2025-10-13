<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('olt_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('sn')->unique(); // Serial Number
            $table->string('management_ip')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('model')->nullable();
            $table->string('pon_type')->nullable(); // GPON, EPON
            $table->integer('pon_port')->nullable();
            $table->integer('ont_id')->nullable();
            $table->string('wifi_ssid')->nullable();
            $table->string('wifi_password')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status')->default('offline'); // online, offline, down
            $table->decimal('rx_power', 5, 2)->nullable(); // Signal RX
            $table->decimal('tx_power', 5, 2)->nullable(); // Signal TX
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onts');
    }
};
