<?php

// 2025_10_07_231511_create_advanced_fiber_infrastructure.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Joint Box / Closure (tempat sambung/splice)
        Schema::create('joint_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type'); // closure, manhole, pole, cabinet
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->integer('capacity')->default(96); // Max splice capacity
            $table->integer('used_capacity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Fiber Cable Segments (per ruas/segment)
        Schema::create('fiber_cable_segments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('cable_type'); // backbone, distribution, drop
            $table->integer('core_count'); // 2, 4, 8, 12, 24, 48, 96, 144
            $table->string('cable_brand')->nullable(); // Corning, Furukawa, etc
            $table->string('cable_model')->nullable();

            // Start Point
            $table->string('start_point_type'); // olt, joint_box, odp
            $table->unsignedBigInteger('start_point_id');
            $table->decimal('start_latitude', 10, 8)->nullable();
            $table->decimal('start_longitude', 11, 8)->nullable();

            // End Point
            $table->string('end_point_type'); // joint_box, odp, ont
            $table->unsignedBigInteger('end_point_id');
            $table->decimal('end_latitude', 10, 8)->nullable();
            $table->decimal('end_longitude', 11, 8)->nullable();

            $table->json('path_coordinates')->nullable(); // GeoJSON LineString
            $table->decimal('distance', 10, 2)->nullable(); // in meters
            $table->string('installation_type')->nullable(); // aerial, underground, duct
            $table->date('installation_date')->nullable();
            $table->string('status')->default('active'); // active, damaged, maintenance
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Fiber Core Mapping (detail per core dalam kabel)
        Schema::create('fiber_cores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cable_segment_id')->constrained('fiber_cable_segments')->onDelete('cascade');
            $table->integer('core_number'); // 1, 2, 3, ... 144
            $table->string('core_color')->nullable(); // Blue, Orange, Green, etc
            $table->string('tube_number')->nullable(); // For ribbon cable
            $table->string('status')->default('available'); // available, used, reserved, damaged

            // Connection info
            $table->string('connected_to_type')->nullable(); // splitter, ont, odp_port
            $table->unsignedBigInteger('connected_to_id')->nullable();

            // Optical Loss
            $table->decimal('loss_db', 5, 2)->nullable(); // Loss in dB
            $table->decimal('length_km', 8, 3)->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['cable_segment_id', 'core_number']);
        });

        // Splice Points (titik sambungan antar kabel)
        Schema::create('fiber_splices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('joint_box_id')->constrained('joint_boxes')->onDelete('cascade');

            // Input Fiber
            $table->foreignId('input_segment_id')->constrained('fiber_cable_segments');
            $table->integer('input_core_number');

            // Output Fiber
            $table->foreignId('output_segment_id')->constrained('fiber_cable_segments');
            $table->integer('output_core_number');

            $table->string('splice_type'); // fusion, mechanical
            $table->decimal('splice_loss', 5, 2)->default(0.1); // in dB
            $table->date('splice_date')->nullable();
            $table->string('technician')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ODP Port Mapping
        Schema::create('odp_ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odp_id')->constrained('odps')->onDelete('cascade');
            $table->integer('port_number');
            $table->string('status')->default('available'); // available, used, reserved, damaged

            // Connected fiber core
            $table->foreignId('fiber_core_id')->nullable()->constrained('fiber_cores')->onDelete('set null');

            // Connected splitter (if any)
            $table->foreignId('splitter_id')->nullable()->constrained('splitters')->onDelete('set null');
            $table->integer('splitter_port')->nullable();

            // Connected ONT
            $table->foreignId('ont_id')->nullable()->constrained('onts')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['odp_id', 'port_number']);
        });

        // Fiber Test Results (OTDR)
        Schema::create('fiber_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiber_core_id')->constrained('fiber_cores')->onDelete('cascade');
            $table->date('test_date');
            $table->string('test_type')->default('OTDR'); // OTDR, Power Meter, Light Source
            $table->decimal('total_loss', 5, 2)->nullable();
            $table->decimal('total_length', 8, 3)->nullable();
            $table->string('status')->default('pass'); // pass, fail, warning
            $table->json('test_data')->nullable(); // Detailed OTDR trace
            $table->string('technician')->nullable();
            $table->string('sor_file')->nullable(); // OTDR file path
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiber_test_results');
        Schema::dropIfExists('odp_ports');
        Schema::dropIfExists('fiber_splices');
        Schema::dropIfExists('fiber_cores');
        Schema::dropIfExists('fiber_cable_segments');
        Schema::dropIfExists('joint_boxes');
    }
};
