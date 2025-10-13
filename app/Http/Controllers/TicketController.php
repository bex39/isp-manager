<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TicketController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_all_tickets', only: ['index', 'show']),
            new Middleware('can:create_ticket', only: ['create', 'store']),
            new Middleware('can:update_ticket', only: ['edit', 'update', 'assign', 'updateStatus']),
            new Middleware('can:delete_ticket', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Ticket::with(['customer', 'assignedTo', 'createdBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned teknisi
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $tickets = $query->latest()->paginate(15);
        $teknisis = User::role('teknisi')->get();

        // Stats
        $stats = [
            'open' => Ticket::where('status', 'open')->count(),
            'in_progress' => Ticket::where('status', 'in_progress')->count(),
            'resolved' => Ticket::where('status', 'resolved')->count(),
            'urgent' => Ticket::where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('tickets.index', compact('tickets', 'teknisis', 'stats'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->get();
        $teknisis = User::role('teknisi')->get();

        return view('tickets.create', compact('customers', 'teknisis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:technical,billing,general,complaint',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $validated['ticket_number'] = Ticket::generateTicketNumber();
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'open';

        $ticket = Ticket::create($validated);

        // Log activity
        ActivityLog::log(
            'created',
            'Ticket',
            $ticket->id,
            "Created ticket {$ticket->ticket_number}: {$ticket->title}",
            [
                'customer_id' => $ticket->customer_id,
                'priority' => $ticket->priority
            ]
        );

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket berhasil dibuat!');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['customer', 'assignedTo', 'createdBy', 'responses.user']);
        $teknisis = User::role('teknisi')->get();

        return view('tickets.show', compact('ticket', 'teknisis'));
    }

    public function addResponse(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_internal' => $request->is_internal ?? false,
        ]);

        // Log activity
        ActivityLog::log(
            'response_added',
            'Ticket',
            $ticket->id,
            auth()->user()->name . " added response to ticket {$ticket->ticket_number}",
            ['is_internal' => $response->is_internal]
        );

        return back()->with('success', 'Response berhasil ditambahkan!');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $oldAssignee = $ticket->assignedTo?->name ?? 'Unassigned';
        $newAssignee = User::find($request->assigned_to);

        $ticket->update(['assigned_to' => $request->assigned_to]);

        // Auto change status to in_progress if still open
        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Log activity
        ActivityLog::log(
            'assigned',
            'Ticket',
            $ticket->id,
            "Ticket {$ticket->ticket_number} assigned from {$oldAssignee} to {$newAssignee->name}",
            [
                'old_assignee' => $oldAssignee,
                'new_assignee' => $newAssignee->name
            ]
        );

        return back()->with('success', "Ticket berhasil diassign ke {$newAssignee->name}!");
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting_customer,resolved,closed',
        ]);

        $oldStatus = $ticket->status;
        $ticket->update(['status' => $request->status]);

        // Set resolved_at timestamp
        if ($request->status === 'resolved' && !$ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        // Set closed_at timestamp
        if ($request->status === 'closed' && !$ticket->closed_at) {
            $ticket->update(['closed_at' => now()]);
        }

        // Log activity
        ActivityLog::log(
            'status_changed',
            'Ticket',
            $ticket->id,
            "Ticket {$ticket->ticket_number} status changed from {$oldStatus} to {$request->status}",
            [
                'old_status' => $oldStatus,
                'new_status' => $request->status
            ]
        );

        return back()->with('success', 'Status ticket berhasil diupdate!');
    }

    public function destroy(Ticket $ticket)
    {
        $ticketNumber = $ticket->ticket_number;
        $ticket->delete();

        // Log activity
        ActivityLog::log(
            'deleted',
            'Ticket',
            $ticket->id,
            "Deleted ticket {$ticketNumber}",
            ['ticket_number' => $ticketNumber]
        );

        return redirect()->route('tickets.index')->with('success', 'Ticket berhasil dihapus!');
    }

    public function downloadPdf(Ticket $ticket)
    {
        $ticket->load(['customer', 'assignedTo', 'createdBy', 'responses.user']);

        $pdf = Pdf::loadView('tickets.pdf', compact('ticket'))
            ->setPaper('a4', 'portrait');

        $filename = $ticket->ticket_number . '.pdf';

        return $pdf->download($filename);
    }
}
