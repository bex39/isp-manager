<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('download_speed'); // Mbps
            $table->integer('upload_speed'); // Mbps
            $table->decimal('price', 10, 2);

            // FUP Settings
            $table->boolean('has_fup')->default(false);
            $table->integer('fup_quota')->nullable(); // GB
            $table->integer('fup_speed')->nullable(); // Speed after FUP (Mbps)

            // Billing
            $table->enum('billing_cycle', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->integer('grace_period')->default(3); // days

            // Advanced
            $table->integer('burst_limit')->nullable(); // Mbps
            $table->integer('priority')->default(5); // QoS priority (1-10)
            $table->integer('connection_limit')->nullable();
            $table->json('available_for')->nullable(); // ['pppoe', 'static', 'hotspot']

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
