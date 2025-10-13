<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('olts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ip_address')->unique();
            $table->integer('telnet_port')->default(23);
            $table->integer('ssh_port')->default(22);
            $table->string('username');
            $table->string('password');
            $table->string('olt_type'); // huawei, zte, fiberhome, etc
            $table->string('model')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('total_ports')->default(16);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('olts');
    }
};
