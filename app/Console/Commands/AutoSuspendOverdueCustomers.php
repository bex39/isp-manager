<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Services\MikrotikService;
use Carbon\Carbon;

class AutoSuspendOverdueCustomers extends Command
{
    protected $signature = 'customers:auto-suspend';
    protected $description = 'Auto suspend customers with overdue invoices';

    public function handle()
    {
        $this->info('Checking for overdue invoices...');

        // Get invoices yang overdue lebih dari grace period (misal 3 hari)
        $gracePeriod = 3;
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->whereDate('due_date', '<', Carbon::now()->subDays($gracePeriod))
            ->with('customer')
            ->get();

        $suspended = 0;
        $errors = [];

        foreach ($overdueInvoices as $invoice) {
            $customer = $invoice->customer;

            // Skip jika sudah suspended
            if ($customer->status === 'suspended') {
                continue;
            }

            try {
                // Suspend customer
                $customer->update(['status' => 'suspended']);

                // Disable di MikroTik jika PPPoE
                if (($customer->connection_type === 'pppoe_direct' || $customer->connection_type === 'pppoe_mikrotik')
                    && $customer->router_id
                    && isset($customer->connection_config['username'])) {

                    $router = $customer->router;
                    $mikrotik = new MikrotikService($router);
                    $mikrotik->disablePPPoEUser($customer->connection_config['username']);
                }

                $suspended++;
                $this->info("✓ Suspended: {$customer->name} (Invoice: {$invoice->invoice_number})");

            } catch (\Exception $e) {
                $errors[] = [
                    'customer' => $customer->name,
                    'error' => $e->getMessage()
                ];
                $this->error("✗ Failed: {$customer->name} - {$e->getMessage()}");
            }
        }

        $this->info("\nSummary:");
        $this->info("Total suspended: {$suspended}");
        $this->error("Total errors: " . count($errors));

        return 0;
    }
}
