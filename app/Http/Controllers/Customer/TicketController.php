<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    private function checkAuth()
    {
        if (!Auth::guard('customer')->check()) {
            return redirect()->route('customer.login');
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $customer = auth('customer')->user();
        $tickets = $customer->tickets()->latest()->paginate(10);

        return view('customers.tickets.index', compact('tickets'));
    }

    public function create()
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        return view('customers.tickets.create');
    }

    public function store(Request $request)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:technical,billing,general,complaint',
        ]);

        $customer = auth('customer')->user();

        $validated['customer_id'] = $customer->id;
        $validated['ticket_number'] = Ticket::generateTicketNumber();
        $validated['status'] = 'open';

        $ticket = Ticket::create($validated);

        ActivityLog::log(
            'created',
            'Ticket',
            $ticket->id,
            "Customer {$customer->name} created ticket {$ticket->ticket_number}",
            ['customer_id' => $customer->id]
        );

        return redirect()->route('customer.tickets.show', $ticket)->with('success', 'Ticket berhasil dibuat!');
    }

    public function show(Ticket $ticket)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        // Check ownership
        if ($ticket->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        $ticket->load(['responses.user']);

        return view('customers.tickets.show', compact('ticket'));
    }

    public function addResponse(Request $request, Ticket $ticket)
    {
        if ($redirect = $this->checkAuth()) return $redirect;

        // Check ownership
        if ($ticket->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => null,
            'message' => $request->message,
            'is_internal' => false,
        ]);

        return back()->with('success', 'Response berhasil ditambahkan!');
    }
}
