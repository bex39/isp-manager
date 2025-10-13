<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('access_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->string('mac_address')->nullable();
            $table->string('brand'); // tplink, unifi, ruijie, mikrotik, etc
            $table->string('model')->nullable();
            $table->string('ssid')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->integer('ssh_port')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('offline'); // online, offline
            $table->integer('ping_latency')->nullable();
            $table->integer('active_clients')->default(0);
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_points');
    }
};
