<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onts', function (Blueprint $table) {
            $table->timestamp('last_provision_at')->nullable()->after('installation_date');
            $table->boolean('auto_provision_enabled')->default(true)->after('is_active');
            $table->foreignId('provision_template_id')->nullable()
                ->constrained('acs_config_templates')
                ->nullOnDelete()
                ->after('auto_provision_enabled');

            // Performance indexes
            $table->index(['sn', 'olt_id']);
            $table->index('last_provision_at');
            $table->index('auto_provision_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('onts', function (Blueprint $table) {
            $table->dropForeign(['provision_template_id']);
            $table->dropIndex(['sn', 'olt_id']);
            $table->dropIndex(['last_provision_at']);
            $table->dropIndex(['auto_provision_enabled']);
            $table->dropColumn([
                'last_provision_at',
                'auto_provision_enabled',
                'provision_template_id',
            ]);
        });
    }
};
