<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $ticket->ticket_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #1e1b4b; padding-bottom: 20px; }
        .header h1 { margin: 0; color: #1e1b4b; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-primary { background: #3b82f6; color: white; }
        .badge-danger { background: #ef4444; color: white; }
        .badge-success { background: #10b981; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .response {
            margin: 15px 0;
            padding: 15px;
            background: #f9fafb;
            border-left: 3px solid #1e1b4b;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>TICKET REPORT</h1>
        <p>{{ $ticket->ticket_number }}</p>
    </div>

    <h3>{{ $ticket->title }}</h3>

    <table>
        <tr>
            <td width="150"><strong>Status:</strong></td>
            <td>
                <span class="badge badge-primary">{{ strtoupper($ticket->status) }}</span>
            </td>
        </tr>
        <tr>
            <td><strong>Priority:</strong></td>
            <td>
                <span class="badge badge-danger">{{ strtoupper($ticket->priority) }}</span>
            </td>
        </tr>
        <tr>
            <td><strong>Category:</strong></td>
            <td>{{ ucfirst($ticket->category) }}</td>
        </tr>
        <tr>
            <td><strong>Customer:</strong></td>
            <td>{{ $ticket->customer->name }} ({{ $ticket->customer->customer_code }})</td>
        </tr>
        <tr>
            <td><strong>Assigned To:</strong></td>
            <td>{{ $ticket->assignedTo?->name ?? 'Unassigned' }}</td>
        </tr>
        <tr>
            <td><strong>Created:</strong></td>
            <td>{{ $ticket->created_at->format('d M Y H:i') }}</td>
        </tr>
    </table>

    <h4>Description</h4>
    <p style="white-space: pre-wrap;">{{ $ticket->description }}</p>

    <h4>Responses ({{ $ticket->responses->count() }})</h4>
    @foreach($ticket->responses as $response)
    <div class="response">
        <strong>{{ $response->user->name }}</strong>
        <small style="color: #666;">- {{ $response->created_at->format('d M Y H:i') }}</small>
        <p style="margin: 10px 0 0 0;">{{ $response->message }}</p>
    </div>
    @endforeach
</body>
</html>
