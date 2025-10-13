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
        Schema::table('fiber_cable_segments', function (Blueprint $table) {
            // Connector type di start point
            if (!Schema::hasColumn('fiber_cable_segments', 'start_connector_type')) {
                $table->string('start_connector_type')->nullable()
                    ->after('start_longitude')
                    ->comment('SC, LC, FC, ST, E2000, MPO');
            }

            // Port number/label di start point
            if (!Schema::hasColumn('fiber_cable_segments', 'start_port')) {
                $table->string('start_port')->nullable()
                    ->after('start_connector_type')
                    ->comment('Port number/label at start point');
            }

            // Connector type di end point
            if (!Schema::hasColumn('fiber_cable_segments', 'end_connector_type')) {
                $table->string('end_connector_type')->nullable()
                    ->after('end_longitude')
                    ->comment('SC, LC, FC, ST, E2000, MPO');
            }

            // Port number/label di end point
            if (!Schema::hasColumn('fiber_cable_segments', 'end_port')) {
                $table->string('end_port')->nullable()
                    ->after('end_connector_type')
                    ->comment('Port number/label at end point');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiber_cable_segments', function (Blueprint $table) {
            $columns = [
                'start_connector_type',
                'start_port',
                'end_connector_type',
                'end_port'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('fiber_cable_segments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
