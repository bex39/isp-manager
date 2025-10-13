<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #1e1b4b;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background: #f9fafb;
        }
        .invoice-details {
            background: white;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #1e1b4b;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #1e1b4b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ISP MANAGER</h1>
            <p>Invoice Reminder</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $invoice->customer->name }}</strong>,</p>

            @if($reminderType === 'due_soon')
            <p>This is a friendly reminder that your invoice will be due soon.</p>
            @elseif($reminderType === 'overdue')
            <div class="alert alert-warning">
                <strong>⚠️ Payment Overdue</strong><br>
                Your invoice is now overdue. Please make payment as soon as possible to avoid service interruption.
            </div>
            @elseif($reminderType === 'final_notice')
            <div class="alert alert-danger">
                <strong>🚨 FINAL NOTICE</strong><br>
                This is your final notice. Your service will be suspended if payment is not received within 24 hours.
            </div>
            @endif

            <div class="invoice-details">
                <h3>Invoice Details</h3>
                <table style="width: 100%;">
                    <tr>
                        <td><strong>Invoice Number:</strong></td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Issue Date:</strong></td>
                        <td>{{ $invoice->issue_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Due Date:</strong></td>
                        <td>{{ $invoice->due_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Amount Due:</strong></td>
                        <td><strong style="font-size: 18px; color: #1e1b4b;">{{ $invoice->getFormattedTotal() }}</strong></td>
                    </tr>
                </table>
            </div>

            <h4>Payment Instructions:</h4>
            <p>Please transfer to:</p>
            <ul>
                <li>Bank BCA - 1234567890</li>
                <li>a.n. ISP MANAGER</li>
                <li>Amount: <strong>{{ $invoice->getFormattedTotal() }}</strong></li>
            </ul>

            <p>After payment, please confirm via WhatsApp or email with payment proof.</p>

            <center>
                <a href="{{ route('invoices.show', $invoice) }}" class="button">View Invoice Online</a>
            </center>

            <p>Thank you for your business!</p>
        </div>

        <div class="footer">
            <p>ISP MANAGER<br>
            Jl. Contoh No. 123, Kuta, Bali<br>
            Phone: 0361-1234567 | Email: billing@ispmanager.com</p>
        </div>
    </div>
</body>
</html>
