<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send invoice payment reminders via Email/WhatsApp';

    public function handle()
    {
        $notificationService = new NotificationService();

        // H-3: Due Soon Reminder
        $dueSoonInvoices = Invoice::where('status', 'unpaid')
            ->whereDate('due_date', Carbon::now()->addDays(3))
            ->get();

        $this->info("Sending 'due soon' reminders to {$dueSoonInvoices->count()} customers...");

        foreach ($dueSoonInvoices as $invoice) {
            $notificationService->sendEmailReminder($invoice, 'due_soon');
            $notificationService->sendWhatsAppReminder($invoice, 'due_soon');
            $this->info("✓ Sent to: {$invoice->customer->name}");
        }

        // H+1: Overdue Reminder
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->whereDate('due_date', Carbon::now()->subDay())
            ->get();

        $this->info("Sending 'overdue' reminders to {$overdueInvoices->count()} customers...");

        foreach ($overdueInvoices as $invoice) {
            // Update status to overdue
            $invoice->update(['status' => 'overdue']);

            $notificationService->sendEmailReminder($invoice, 'overdue');
            $notificationService->sendWhatsAppReminder($invoice, 'overdue');
            $this->info("✓ Sent to: {$invoice->customer->name}");
        }

        // H+7: Final Notice
        $finalNoticeInvoices = Invoice::where('status', 'overdue')
            ->whereDate('due_date', Carbon::now()->subDays(7))
            ->get();

        $this->info("Sending 'final notice' to {$finalNoticeInvoices->count()} customers...");

        foreach ($finalNoticeInvoices as $invoice) {
            $notificationService->sendEmailReminder($invoice, 'final_notice');
            $notificationService->sendWhatsAppReminder($invoice, 'final_notice');
            $this->info("✓ Sent to: {$invoice->customer->name}");
        }

        $this->info('All reminders sent successfully!');
        return 0;
    }
}
