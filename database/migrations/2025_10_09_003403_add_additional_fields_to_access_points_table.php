<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('access_points', function (Blueprint $table) {
            // WiFi fields
            if (!Schema::hasColumn('access_points', 'wifi_password')) {
                $table->string('wifi_password')->nullable()->after('ssid');
            }
            if (!Schema::hasColumn('access_points', 'frequency')) {
                $table->string('frequency', 50)->nullable()->after('wifi_password');
            }

            // Client management
            if (!Schema::hasColumn('access_points', 'max_clients')) {
                $table->integer('max_clients')->nullable()->after('frequency');
            }
            if (!Schema::hasColumn('access_points', 'connected_clients')) {
                $table->integer('connected_clients')->default(0)->after('max_clients');
            }

            // Status fields
            if (!Schema::hasColumn('access_points', 'status')) {
                $table->string('status', 20)->default('offline')->after('connected_clients');
            }
            if (!Schema::hasColumn('access_points', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('status');
            }
            if (!Schema::hasColumn('access_points', 'ping_latency')) {
                $table->float('ping_latency', 8, 2)->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('access_points', 'last_seen')) {
                $table->timestamp('last_seen')->nullable()->after('ping_latency');
            }

            // Location fields
            if (!Schema::hasColumn('access_points', 'address')) {
                $table->text('address')->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropColumn([
                'wifi_password',
                'frequency',
                'max_clients',
                'connected_clients',
                'status',
                'is_active',
                'ping_latency',
                'last_seen',
                'address'
            ]);
        });
    }
};
