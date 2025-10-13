<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
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
        return Customer::with('package', 'router')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Customer Code',
            'Name',
            'Email',
            'Phone',
            'Package',
            'Status',
            'Router',
            'Installation Date',
            'Created At',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->customer_code,
            $customer->name,
            $customer->email,
            $customer->phone,
            $customer->package->name,
            ucfirst($customer->status),
            $customer->router ? $customer->router->name : '-',
            $customer->installation_date->format('Y-m-d'),
            $customer->created_at->format('Y-m-d'),
        ];
    }

    public function title(): string
    {
        return 'Customer Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
