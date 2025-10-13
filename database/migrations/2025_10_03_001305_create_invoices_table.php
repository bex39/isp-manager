<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            // Invoice Details
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('paid_at')->nullable();

            // Amounts
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Status
            $table->enum('status', ['unpaid', 'paid', 'overdue', 'cancelled'])->default('unpaid');

            // Payment Info
            $table->string('payment_method')->nullable(); // xendit, manual, cash
            $table->string('payment_channel')->nullable(); // va_bca, qris, ovo, etc
            $table->string('payment_reference')->nullable(); // transaction ID dari payment gateway
            $table->text('payment_details')->nullable(); // JSON untuk detail payment

            // Notes
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();

            // Items (JSON)
            $table->json('items'); // [{description, qty, price, amount}]

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('invoice_number');
            $table->index('customer_id');
            $table->index('status');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
