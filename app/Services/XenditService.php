<?php

namespace App\Services;

use App\Models\Invoice;
use Xendit\Xendit;

class XenditService
{
    public function __construct()
    {
        Xendit::setApiKey(config('xendit.secret_key'));
    }

    /**
     * Create Virtual Account
     */
    public function createVirtualAccount(Invoice $invoice, string $bankCode = 'BCA')
    {
        $params = [
            'external_id' => $invoice->invoice_number,
            'bank_code' => $bankCode, // BCA, BNI, BRI, MANDIRI, PERMATA
            'name' => $invoice->customer->name,
            'expected_amount' => (int) $invoice->total,
            'is_closed' => true, // Amount tetap
            'expiration_date' => $invoice->due_date->endOfDay()->toIso8601String(),
            'is_single_use' => true,
        ];

        $virtualAccount = \Xendit\VirtualAccounts::create($params);

        // Save to invoice
        $invoice->update([
            'payment_method' => 'xendit_va',
            'payment_channel' => $bankCode,
            'payment_details' => [
                'va_number' => $virtualAccount['account_number'],
                'bank_code' => $bankCode,
                'xendit_id' => $virtualAccount['id'],
            ]
        ]);

        return $virtualAccount;
    }

    /**
     * Create QRIS Payment
     */
    public function createQRIS(Invoice $invoice)
    {
        $params = [
            'external_id' => $invoice->invoice_number,
            'type' => 'DYNAMIC',
            'callback_url' => config('xendit.webhook_url'),
            'amount' => (int) $invoice->total,
        ];

        $qrCode = \Xendit\QRCode::create($params);

        $invoice->update([
            'payment_method' => 'xendit_qris',
            'payment_channel' => 'qris',
            'payment_details' => [
                'qr_string' => $qrCode['qr_string'],
                'qr_url' => $qrCode['qr_url'],
                'xendit_id' => $qrCode['id'],
            ]
        ]);

        return $qrCode;
    }

    /**
     * Create E-Wallet Payment (OVO, DANA, LINKAJA, SHOPEEPAY)
     */
    public function createEWallet(Invoice $invoice, string $ewalletType = 'OVO')
    {
        $params = [
            'external_id' => $invoice->invoice_number,
            'amount' => (int) $invoice->total,
            'phone' => $this->formatPhone($invoice->customer->phone),
            'ewallet_type' => $ewalletType, // OVO, DANA, LINKAJA, SHOPEEPAY
            'callback_url' => config('xendit.webhook_url'),
            'redirect_url' => route('invoices.show', $invoice),
        ];

        $ewallet = \Xendit\EWallets::create($params);

        $invoice->update([
            'payment_method' => 'xendit_ewallet',
            'payment_channel' => strtolower($ewalletType),
            'payment_details' => [
                'checkout_url' => $ewallet['actions']['desktop_web_checkout_url'] ?? null,
                'mobile_url' => $ewallet['actions']['mobile_web_checkout_url'] ?? null,
                'xendit_id' => $ewallet['id'],
            ]
        ]);

        return $ewallet;
    }

    /**
     * Format phone number for Xendit (62xxx)
     */
    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }
}
