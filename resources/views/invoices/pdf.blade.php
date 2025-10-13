<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #1e1b4b;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1e1b4b;
            font-size: 28px;
        }
        .company-info {
            margin-top: 10px;
            color: #666;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .invoice-info > div {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #1e1b4b;
        }
        .status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status.paid { background: #10b981; color: white; }
        .status.unpaid { background: #f59e0b; color: white; }
        .status.overdue { background: #ef4444; color: white; }
        .status.cancelled { background: #6b7280; color: white; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            font-weight: bold;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .summary {
            width: 50%;
            margin-left: auto;
            margin-top: 20px;
        }
        .summary table {
            width: 100%;
        }
        .summary td {
            padding: 5px 10px;
        }
        .summary .total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #1e1b4b;
            padding-top: 10px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background: #f9fafb;
            border-left: 3px solid #1e1b4b;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <div class="company-info">
                <strong>ISP MANAGER</strong><br>
                Jl. Contoh No. 123, Kuta, Bali<br>
                Phone: 0361-1234567 | Email: billing@ispmanager.com
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div>
                <h3>Bill To:</h3>
                <strong>{{ $invoice->customer->name }}</strong><br>
                {{ $invoice->customer->customer_code }}<br>
                {{ $invoice->customer->address }}<br>
                Phone: {{ $invoice->customer->phone }}<br>
                @if($invoice->customer->email)
                Email: {{ $invoice->customer->email }}
                @endif
            </div>
            <div style="text-align: right;">
                <h3>Invoice Details:</h3>
                <strong>{{ $invoice->invoice_number }}</strong><br>
                <span class="status {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span><br><br>
                <strong>Issue Date:</strong> {{ $invoice->issue_date->format('d M Y') }}<br>
                <strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}<br>
                @if($invoice->paid_at)
                <strong>Paid Date:</strong> {{ $invoice->paid_at->format('d M Y') }}
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center" style="width: 10%;">Qty</th>
                    <th class="text-right" style="width: 20%;">Price</th>
                    <th class="text-right" style="width: 20%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td class="text-center">{{ $item['qty'] }}</td>
                    <td class="text-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div class="summary">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->tax > 0)
                <tr>
                    <td>Tax ({{ number_format($invoice->tax_percentage, 2) }}%):</td>
                    <td class="text-right">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right" style="color: #ef4444;">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->late_fee > 0)
                <tr>
                    <td>Late Fee:</td>
                    <td class="text-right" style="color: #f59e0b;">Rp {{ number_format($invoice->late_fee, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total">
                    <td><strong>Total:</strong></td>
                    <td class="text-right"><strong>Rp {{ number_format($invoice->total, 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="notes">
            <strong>Notes:</strong><br>
            {{ $invoice->notes }}
        </div>
        @endif

        <!-- Payment Info -->
        @if($invoice->isPaid())
        <div style="margin-top: 20px; padding: 15px; background: #d1fae5; border: 1px solid #10b981; border-radius: 5px;">
            <strong style="color: #10b981;">✓ PAYMENT RECEIVED</strong><br>
            Payment Method: {{ ucfirst($invoice->payment_method) }}<br>
            @if($invoice->payment_reference)
            Reference: {{ $invoice->payment_reference }}<br>
            @endif
            Paid Date: {{ $invoice->paid_at->format('d M Y H:i') }}
        </div>
        @else
        <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 5px;">
            <strong>Payment Instructions:</strong><br>
            Please transfer to:<br>
            Bank BCA - 1234567890<br>
            a.n. ISP MANAGER<br>
            Amount: <strong>Rp {{ number_format($invoice->total, 0, ',', '.') }}</strong>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
        </div>
    </div>
</body>
</html>
