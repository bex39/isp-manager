<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('splitters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odp_id')->constrained('odps')->onDelete('cascade');
            $table->string('name');
            $table->string('ratio'); // 1:8, 1:16, 1:32, etc
            $table->integer('input_ports')->default(1);
            $table->integer('output_ports')->default(8);
            $table->integer('used_outputs')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('splitters');
    }
};
