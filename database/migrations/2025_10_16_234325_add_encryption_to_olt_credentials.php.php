<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\OLT;

return new class extends Migration
{
    public function up(): void
    {
        // Note: In Laravel 12, we use encrypted cast in model
        // This migration just ensures column types are correct

        // If you want to encrypt existing data:
        $olts = OLT::all();
        foreach ($olts as $olt) {
            if ($olt->username && !str_starts_with($olt->username, 'eyJ')) {
                $olt->username = encrypt($olt->username);
                $olt->password = encrypt($olt->password);
                $olt->saveQuietly(); // Save without events
            }
        }
    }

    public function down(): void
    {
        // Decrypt if rollback
        $olts = OLT::all();
        foreach ($olts as $olt) {
            if ($olt->username && str_starts_with($olt->username, 'eyJ')) {
                try {
                    $olt->username = decrypt($olt->username);
                    $olt->password = decrypt($olt->password);
                    $olt->saveQuietly();
                } catch (\Exception $e) {
                    // Skip if already decrypted
                }
            }
        }
    }
};
