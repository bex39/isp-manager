<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ACS Device Sessions
        Schema::create('acs_device_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ont_id')->constrained('onts')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->ipAddress('remote_ip')->nullable();
            $table->timestamp('last_inform')->nullable();
            $table->timestamp('last_boot')->nullable();
            $table->integer('inform_interval')->default(300);
            $table->json('parameters')->nullable();
            $table->timestamps();

            $table->index(['ont_id', 'last_inform']);
            $table->index('session_id');
        });

        // 2. ACS Config Templates
        Schema::create('acs_config_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type'); // wifi, vlan, port, service_profile, custom
            $table->text('description')->nullable();
            $table->json('parameters');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
            $table->index('code');
        });

        // 3. ACS Config History
        Schema::create('acs_config_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ont_id')->constrained('onts')->onDelete('cascade');
            $table->foreignId('template_id')->nullable()->constrained('acs_config_templates')->nullOnDelete();
            $table->string('action');
            $table->json('parameters')->nullable();
            $table->json('result')->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['ont_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('action');
        });

        // 4. ACS Bulk Operations
        Schema::create('acs_bulk_operations', function (Blueprint $table) {
            $table->id();
            $table->string('operation_name');
            $table->string('operation_type');
            $table->json('target_filter');
            $table->json('parameters')->nullable();
            $table->integer('total_devices')->default(0);
            $table->integer('processed_devices')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->integer('progress_percentage')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('operation_type');
        });

        // 5. ACS Bulk Operation Details
        Schema::create('acs_bulk_operation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bulk_operation_id')->constrained('acs_bulk_operations')->onDelete('cascade');
            $table->foreignId('ont_id')->constrained('onts')->onDelete('cascade');
            $table->enum('status', ['pending', 'processing', 'success', 'failed'])->default('pending');
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['bulk_operation_id', 'status']);
            $table->index('ont_id');
        });

        // 6. ACS Alert Rules
        Schema::create('acs_alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('condition_type');
            $table->json('condition_parameters');
            $table->json('notification_channels');
            $table->json('recipients');
            $table->integer('check_interval')->default(300);
            $table->integer('cooldown_period')->default(3600);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'condition_type']);
        });

        // 7. ACS Alerts
        Schema::create('acs_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('acs_alert_rules')->onDelete('cascade');
            $table->foreignId('ont_id')->constrained('onts')->onDelete('cascade');
            $table->string('alert_type');
            $table->string('severity');
            $table->text('message');
            $table->json('details')->nullable();
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['new', 'acknowledged', 'resolved', 'auto_resolved'])->default('new');
            $table->timestamps();

            $table->index(['ont_id', 'status', 'triggered_at']);
            $table->index(['status', 'severity', 'triggered_at']);
        });

        // 8. ACS Provisioning Queue
        Schema::create('acs_provisioning_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ont_id')->nullable()->constrained('onts')->onDelete('cascade');
            $table->foreignId('olt_id')->constrained('olts')->onDelete('cascade');
            $table->string('sn');
            $table->integer('pon_port')->nullable();
            $table->integer('ont_id_number')->nullable();
            $table->string('provision_type');
            $table->json('config_data')->nullable();
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority', 'scheduled_at']);
            $table->index(['ont_id', 'status']);
            $table->index('sn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acs_provisioning_queue');
        Schema::dropIfExists('acs_alerts');
        Schema::dropIfExists('acs_alert_rules');
        Schema::dropIfExists('acs_bulk_operation_details');
        Schema::dropIfExists('acs_bulk_operations');
        Schema::dropIfExists('acs_config_history');
        Schema::dropIfExists('acs_config_templates');
        Schema::dropIfExists('acs_device_sessions');
    }
};
