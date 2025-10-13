<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20);
            $table->text('address');
            $table->string('id_card_number', 50)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Connection Configuration
            $table->enum('connection_type', ['pppoe_direct', 'pppoe_mikrotik', 'static_ip', 'hotspot', 'dhcp']);
            $table->json('connection_config')->nullable(); // Dynamic config per type

            // Package & Billing
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->integer('custom_speed_download')->nullable(); // Mbps
            $table->integer('custom_speed_upload')->nullable(); // Mbps
            $table->date('installation_date')->nullable();
            $table->date('next_billing_date')->nullable();

            // Router Assignment
            $table->foreignId('router_id')->nullable()->constrained('routers')->nullOnDelete();

            // OLT/Fiber Info (jika fiber)
            $table->foreignId('olt_id')->nullable()->constrained('olts')->nullOnDelete();
            $table->string('ont_serial_number')->nullable();
            $table->string('pon_port')->nullable(); // e.g., 0/1/1

            // Customer MikroTik (jika pakai)
            $table->string('customer_mikrotik_ip')->nullable();
            $table->string('customer_mikrotik_username')->nullable();
            $table->string('customer_mikrotik_password')->nullable();
            $table->string('customer_mikrotik_version')->nullable(); // ROS 6 or 7

            // Status
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->text('notes')->nullable();

            // Assigned Teknisi
            $table->foreignId('assigned_teknisi_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('customer_code');
            $table->index('status');
            $table->index('connection_type');
            $table->index('next_billing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
