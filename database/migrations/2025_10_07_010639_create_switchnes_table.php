<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('switches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address');
            $table->string('mac_address')->nullable();
            $table->string('brand'); // cisco, mikrotik, tplink, dlink, etc
            $table->string('model')->nullable();
            $table->string('username');
            $table->string('password');
            $table->integer('ssh_port')->default(22);
            $table->integer('port_count')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('offline'); // online, offline
            $table->integer('ping_latency')->nullable(); // in ms
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('switches');
    }
};
