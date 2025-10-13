<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InvoiceGeneratorService;

class GenerateMonthlyInvoices extends Command
{
    protected $signature = 'invoices:generate-monthly';
    protected $description = 'Generate monthly invoices for all active customers';

    public function handle()
    {
        $this->info('Starting invoice generation...');

        $service = new InvoiceGeneratorService();
        $result = $service->generateMonthlyInvoices();

        $this->info("Generated: {$result['generated']} invoices");
        $this->warn("Skipped: {$result['skipped']} customers (already have invoice this month)");

        if (count($result['errors']) > 0) {
            $this->error("Errors: " . count($result['errors']));
            foreach ($result['errors'] as $error) {
                $this->error("- {$error['customer']}: {$error['error']}");
            }
        }

        $this->info('Invoice generation completed!');
        return 0;
    }
}
