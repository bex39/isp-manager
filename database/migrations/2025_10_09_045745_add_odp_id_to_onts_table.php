<?php

// 2025_10_09_045745_add_odp_id_to_onts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onts', function (Blueprint $table) {
            // Add ODP relationship
            if (!Schema::hasColumn('onts', 'odp_id')) {
                $table->foreignId('odp_id')->nullable()->after('olt_id')->constrained('odps')->nullOnDelete();
            }

            // Add ODP port number
            if (!Schema::hasColumn('onts', 'odp_port')) {
                $table->integer('odp_port')->nullable()->after('odp_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('onts', function (Blueprint $table) {
            $table->dropForeign(['odp_id']);
            $table->dropColumn(['odp_id', 'odp_port']);
        });
    }
};
