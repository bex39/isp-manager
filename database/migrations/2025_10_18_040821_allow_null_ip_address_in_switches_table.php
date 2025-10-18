<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('switches', function (Blueprint $table) {
            $table->string('ip_address', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('switches', function (Blueprint $table) {
            $table->string('ip_address', 255)->nullable(false)->change();
        });
    }
};
