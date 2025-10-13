<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceGeneratorService
{
    /**
     * Generate invoice untuk semua active customers
     */
    public function generateMonthlyInvoices()
    {
        $customers = Customer::where('status', 'active')
            ->whereNotNull('package_id')
            ->get();

        $generated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($customers as $customer) {
            try {
                // Cek apakah sudah ada invoice bulan ini
                $existingInvoice = Invoice::where('customer_id', $customer->id)
                    ->whereMonth('issue_date', now()->month)
                    ->whereYear('issue_date', now()->year)
                    ->first();

                if ($existingInvoice) {
                    $skipped++;
                    continue;
                }

                // Generate invoice
                $this->generateInvoiceForCustomer($customer);
                $generated++;

            } catch (\Exception $e) {
                $errors[] = [
                    'customer' => $customer->name,
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'generated' => $generated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Generate invoice untuk customer tertentu
     */
    public function generateInvoiceForCustomer(Customer $customer)
    {
        $package = $customer->package;

        if (!$package) {
            throw new \Exception("Customer tidak memiliki package");
        }

        // Hitung billing date
        $issueDate = now();
        $dueDate = now()->addDays($package->grace_period ?? 7);

        // Prepare items
        $items = [
            [
                'description' => $package->name . ' - ' . now()->format('F Y'),
                'qty' => 1,
                'price' => $package->price,
                'amount' => $package->price,
            ]
        ];

        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $customer->id,
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'subtotal' => $package->price,
            'tax_percentage' => 0, // Bisa disesuaikan
            'tax' => 0,
            'discount' => 0,
            'late_fee' => 0,
            'total' => $package->price,
            'status' => 'unpaid',
            'items' => $items,
            'notes' => 'Auto-generated monthly invoice',
        ]);

        // Update customer next billing date
        $customer->update([
            'next_billing_date' => $customer->next_billing_date->addMonth()
        ]);

        return $invoice;
    }
}
