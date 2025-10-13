<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ODF - Optical Distribution Frame (Patch Panel di Central Office)
        Schema::create('odfs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ODF-CO-A, ODF-Central-1
            $table->string('code')->unique(); // ODF-001
            $table->foreignId('olt_id')->constrained('olts')->onDelete('cascade');
            $table->string('location')->default('indoor'); // indoor, outdoor
            $table->integer('total_ports')->default(48); // 24, 48, 96, 144
            $table->integer('used_ports')->default(0);
            $table->string('rack_number')->nullable(); // Rack A, Rack B
            $table->string('position')->nullable(); // U1-U2, Top, Middle, Bottom
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->date('installation_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ODC - Optical Distribution Cabinet (Cabinet Outdoor)
        Schema::create('odcs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ODC-Area-A, ODC-Zone-1
            $table->string('code')->unique(); // ODC-001
            $table->foreignId('odf_id')->nullable()->constrained('odfs')->onDelete('set null');
            $table->string('type')->default('outdoor_cabinet'); // outdoor_cabinet, indoor_cabinet
            $table->integer('total_ports')->default(144); // 96, 144, 288, 576
            $table->integer('used_ports')->default(0);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->date('installation_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odcs');
        Schema::dropIfExists('odfs');
    }
};
