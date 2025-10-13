<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ODP (Optical Distribution Point)
        Schema::create('odps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('olt_id')->nullable()->constrained('olts')->nullOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->integer('total_ports')->default(8);
            $table->integer('used_ports')->default(0);
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Splitters
        Schema::create('splitters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('odp_id')->nullable()->constrained('odps')->nullOnDelete();
            $table->string('type'); // 1:2, 1:4, 1:8, 1:16, 1:32
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('total_ports');
            $table->integer('used_ports')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Cable Routes
        Schema::create('cable_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // backbone, distribution, drop
            $table->foreignId('from_device_id')->nullable();
            $table->string('from_device_type')->nullable(); // Router, OLT, ODP
            $table->foreignId('to_device_id')->nullable();
            $table->string('to_device_type')->nullable();
            $table->json('path_coordinates'); // Array of lat,lng points
            $table->integer('distance_meters')->nullable();
            $table->string('cable_type')->nullable(); // fiber count
            $table->string('color')->default('#000000');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cable_routes');
        Schema::dropIfExists('splitters');
        Schema::dropIfExists('odps');
    }
};
