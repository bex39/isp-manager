<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Invoice::with('customer', 'payments')
            ->whereBetween('issue_date', [$this->startDate, $this->endDate])
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Customer',
            'Issue Date',
            'Due Date',
            'Subtotal',
            'Tax',
            'Total',
            'Status',
            'Paid Amount',
            'Payment Date',
            'Payment Method',
        ];
    }

    public function map($invoice): array
    {
        $payment = $invoice->payments->first();

        return [
            $invoice->invoice_number,
            $invoice->customer->name,
            $invoice->issue_date->format('Y-m-d'),
            $invoice->due_date->format('Y-m-d'),
            $invoice->subtotal,
            $invoice->tax,
            $invoice->total,
            ucfirst($invoice->status),
            $payment ? $payment->amount : 0,
            $payment ? $payment->payment_date->format('Y-m-d') : '-',
            $payment ? ucfirst($payment->payment_method) : '-',
        ];
    }

    public function title(): string
    {
        return 'Financial Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
