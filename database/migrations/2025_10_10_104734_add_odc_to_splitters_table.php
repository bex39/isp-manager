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
        Schema::table('splitters', function (Blueprint $table) {
            // Add ODC relationship
            if (!Schema::hasColumn('splitters', 'odc_id')) {
                $table->foreignId('odc_id')->nullable()->after('odp_id')
                    ->constrained('odcs')->onDelete('set null');
            }

            // Add ODC port number
            if (!Schema::hasColumn('splitters', 'odc_port')) {
                $table->integer('odc_port')->nullable()->after('odc_id')
                    ->comment('Port number di ODC');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('splitters', function (Blueprint $table) {
            if (Schema::hasColumn('splitters', 'odc_id')) {
                $table->dropForeign(['odc_id']);
                $table->dropColumn('odc_id');
            }

            if (Schema::hasColumn('splitters', 'odc_port')) {
                $table->dropColumn('odc_port');
            }
        });
    }
};
