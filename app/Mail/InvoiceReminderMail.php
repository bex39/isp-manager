<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $reminderType = 'due_soon' // due_soon, overdue, final_notice
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->reminderType) {
            'due_soon' => 'Reminder: Invoice ' . $this->invoice->invoice_number . ' Due Soon',
            'overdue' => 'OVERDUE: Invoice ' . $this->invoice->invoice_number,
            'final_notice' => 'FINAL NOTICE: Invoice ' . $this->invoice->invoice_number,
            default => 'Invoice Reminder',
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-reminder',
        );
    }

    public function attachments(): array
    {
        // Attach PDF invoice
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $this->invoice])
            ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(fn () => $pdf->output(), $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
