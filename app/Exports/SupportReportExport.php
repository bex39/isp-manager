<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupportReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles
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
        return Ticket::with('customer', 'assignedUser')
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Ticket #',
            'Customer',
            'Title',
            'Category',
            'Priority',
            'Status',
            'Assigned To',
            'Created At',
            'Resolved At',
            'Resolution Time (Hours)',
        ];
    }

    public function map($ticket): array
    {
        $resolutionTime = null;
        if ($ticket->resolved_at) {
            $resolutionTime = $ticket->created_at->diffInHours($ticket->resolved_at);
        }

        return [
            $ticket->ticket_number,
            $ticket->customer->name,
            $ticket->title,
            ucfirst($ticket->category),
            ucfirst($ticket->priority),
            ucfirst($ticket->status),
            $ticket->assignedUser ? $ticket->assignedUser->name : 'Unassigned',
            $ticket->created_at->format('Y-m-d H:i'),
            $ticket->resolved_at ? $ticket->resolved_at->format('Y-m-d H:i') : '-',
            $resolutionTime ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Support Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
