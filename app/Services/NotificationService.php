<?php

namespace App\Services;

use App\Models\Invoice;
use App\Mail\InvoiceReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class NotificationService
{
    /**
     * Send email reminder
     */
    public function sendEmailReminder(Invoice $invoice, string $reminderType = 'due_soon')
    {
        if (!$invoice->customer->email) {
            return false;
        }

        try {
            Mail::to($invoice->customer->email)
                ->send(new InvoiceReminderMail($invoice, $reminderType));

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send WhatsApp reminder via Fonnte API
     * Register at https://fonnte.com untuk dapat token
     */
    public function sendWhatsAppReminder(Invoice $invoice, string $reminderType = 'due_soon')
    {
        $phone = $this->formatPhoneNumber($invoice->customer->phone);

        if (!$phone) {
            return false;
        }

        $message = $this->getWhatsAppMessage($invoice, $reminderType);

        try {
            // Fonnte API (atau provider lain seperti Wablas, Woowa)
            $response = Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'), // Tambahkan di .env
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error('Failed to send WhatsApp: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format phone number untuk WhatsApp (62xxx)
     */
    private function formatPhoneNumber($phone)
    {
        // Remove spaces, dashes, etc
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert 08xx to 628xx
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }

        // Must start with 62
        if (substr($phone, 0, 2) !== '62') {
            return null;
        }

        return $phone;
    }

    /**
     * Get WhatsApp message template
     */
    private function getWhatsAppMessage(Invoice $invoice, string $reminderType)
    {
        $customer = $invoice->customer;
        $invoiceUrl = route('invoices.show', $invoice);

        $message = "Halo *{$customer->name}*,\n\n";

        if ($reminderType === 'due_soon') {
            $message .= "Kami ingin mengingatkan bahwa invoice Anda akan jatuh tempo dalam 3 hari.\n\n";
        } elseif ($reminderType === 'overdue') {
            $message .= "⚠️ Invoice Anda telah *JATUH TEMPO*.\n\n";
        } elseif ($reminderType === 'final_notice') {
            $message .= "🚨 *PERINGATAN TERAKHIR*\n";
            $message .= "Layanan Anda akan diputus dalam 24 jam jika pembayaran tidak diterima.\n\n";
        }

        $message .= "*Detail Invoice:*\n";
        $message .= "No. Invoice: {$invoice->invoice_number}\n";
        $message .= "Tanggal Jatuh Tempo: {$invoice->due_date->format('d M Y')}\n";
        $message .= "Total Tagihan: *{$invoice->getFormattedTotal()}*\n\n";
        $message .= "*Transfer ke:*\n";
        $message .= "Bank BCA - 1234567890\n";
        $message .= "a.n. ISP MANAGER\n\n";
        $message .= "Setelah transfer, mohon kirim bukti pembayaran ke nomor ini.\n\n";
        $message .= "Terima kasih!\n";
        $message .= "_ISP MANAGER_";

        return $message;
    }
}
