<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $payment->payment_number }}</title>
    <style>
        @page {
            margin: 15px;
            header: html_MyHeader;
            footer: html_MyFooter;
        }
        
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        
        /* Header */
        html_MyHeader {
            position: running(header);
        }
        
        .page-header {
            border-bottom: 2px solid #FE171B;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        
        .logo-section {
            float: left;
            width: 50%;
        }
        
        .logo-section img {
            max-height: 35px;
            width: auto;
        }
        
        .company-info {
            float: right;
            width: 45%;
            text-align: right;
            font-size: 8px;
            color: #64748b;
            line-height: 1.3;
        }
        
        .company-info strong {
            color: #1e293b;
            font-size: 9px;
        }
        
        .clearfix {
            clear: both;
        }
        
        /* Title */
        .doc-title {
            text-align: center;
            margin: 15px 0;
        }
        
        .doc-title h1 {
            font-size: 24px;
            font-weight: 800;
            color: #FE171B;
            letter-spacing: 3px;
            margin: 0;
            padding: 6px 20px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            display: inline-block;
            background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
        }
        
        /* Two Columns */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        
        .col-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        
        .col-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 10px;
        }
        
        /* Info Box */
        .info-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
        }
        
        .info-title {
            font-size: 9px;
            font-weight: 700;
            color: #FE171B;
            margin-bottom: 6px;
            text-transform: uppercase;
            border-bottom: 1px solid #FE171B;
            padding-bottom: 3px;
            display: inline-block;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 85px;
            font-weight: 600;
            color: #475569;
            padding: 3px 0;
            font-size: 9px;
        }
        
        .info-value {
            display: table-cell;
            color: #1e293b;
            padding: 3px 0;
            font-size: 9px;
        }
        
        /* Client Box */
        .client-box {
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
            border-left: 3px solid #FE171B;
            border-radius: 8px;
            padding: 8px 12px;
        }
        
        .client-box h3 {
            font-size: 9px;
            font-weight: 700;
            color: #FE171B;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        
        .client-details {
            font-size: 9px;
            line-height: 1.4;
        }
        
        /* Payment Table */
        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .payment-table th {
            background: #1e293b;
            color: white;
            padding: 6px 8px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
        }
        
        .payment-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }
        
        .payment-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Payment Method Box */
        .method-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 12px;
        }
        
        .method-grid {
            display: table;
            width: 100%;
        }
        
        .method-row {
            display: table-row;
        }
        
        .method-label {
            display: table-cell;
            width: 80px;
            font-weight: 600;
            color: #475569;
            padding: 3px 0;
            font-size: 9px;
        }
        
        .method-value {
            display: table-cell;
            color: #1e293b;
            padding: 3px 0;
            font-size: 9px;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
        }
        
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-completed { background: #dcfce7; color: #15803d; }
        .status-failed { background: #fee2e2; color: #dc2626; }
        
        /* Footer */
        html_MyFooter {
            position: running(footer);
        }
        
        .page-footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 7px;
            color: #94a3b8;
        }
        
        .section-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 10px 0;
        }
        
        .amount-total {
            font-size: 11px;
            font-weight: 700;
            color: #FE171B;
        }
        
        /* Clean text styling - no icons */
        .clean-text {
            font-weight: normal;
        }
        
        ul {
            margin: 5px 0 0 15px;
            padding: 0;
            font-size: 8px;
            color: #78350f;
        }
        
        li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

<!-- Header -->
<html_MyHeader>
    <div class="page-header">
        <div class="logo-section">
            <img src="{{ public_path('logo.png') }}" alt="WebInventorz" height="30">
        </div>
        <div class="company-info">
            <strong>WebInventorz.com</strong><br>
            info@webinventorz.com | +92 300 123 4567<br>
            Lahore, Pakistan
        </div>
        <div class="clearfix"></div>
    </div>
</html_MyHeader>

<!-- Title -->
<div class="doc-title">
    <h1>PAYMENT RECEIPT</h1>
</div>

<!-- Two Columns: Payment Info & Client Info -->
<div class="two-columns">
    <div class="col-left">
        <div class="info-box">
            <div class="info-title">PAYMENT INFORMATION</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Payment Number:</div>
                    <div class="info-value"><strong>{{ $payment->payment_number }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Date:</div>
                    <div class="info-value">{{ $payment->payment_date->format('F d, Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Transaction ID:</div>
                    <div class="info-value">{{ $payment->transaction_id ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Receipt Number:</div>
                    <div class="info-value">{{ $payment->receipt_number ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $payment->status }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-right">
        <div class="client-box">
            <h3>RECEIVED FROM</h3>
            <div class="client-details">
                <strong>{{ $client->name }}</strong><br>
                {{ $client->email }}<br>
                @if($client->phone) {{ $client->phone }}<br> @endif
                @if($client->company) {{ $client->company }}<br> @endif
                @if($client->address) {{ $client->address }} @endif
            </div>
        </div>
    </div>
</div>

<!-- Section Divider -->
<div class="section-divider"></div>

<!-- Payment Details Table -->
<table class="payment-table">
    <thead>
        <tr>
            <th style="width: 70%">Description</th>
            <th style="width: 30%">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <strong>Project:</strong> {{ $project->name }}<br>
                <strong>Project Code:</strong> {{ $project->project_code }}
                @if($payment->milestone)
                    <br><strong>Milestone:</strong> {{ $payment->milestone->milestone_name }}
                @endif
             </td
            <td><strong>${{ number_format($payment->amount, 2) }}</strong></td>
        </tr>
    </tbody>
    <tfoot>
        <tr style="background: #f1f5f9;">
            <td style="text-align: right; font-weight: 700;">TOTAL PAID:</td>
            <td style="text-align: right; font-weight: 700; color: #FE171B;">${{ number_format($payment->amount, 2) }}</td>
        </tr>
    </tfoot>
</table>

<!-- Payment Method Details -->
<div class="method-box">
    <div class="method-grid">
        <div class="method-row">
            <div class="method-label">Payment Method:</div>
            <div class="method-value">
                @php
                    $methodNames = [
                        'cash' => 'Cash',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'stripe' => 'Stripe',
                        'jazzcash' => 'JazzCash',
                        'easypaisa' => 'EasyPaisa'
                    ];
                @endphp
                {{ $methodNames[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
            </div>
        </div>
        @if($payment->payment_gateway_id)
        <div class="method-row">
            <div class="method-label">Payment Gateway:</div>
            <div class="method-value">{{ $payment->paymentGateway->name ?? 'N/A' }}</div>
        </div>
        @endif
    </div>
</div>

<!-- Notes -->
@if($payment->notes)
<div class="method-box" style="margin-top: 8px; background: #fef2f2;">
    <div class="method-grid">
        <div class="method-row">
            <div class="method-label">Notes:</div>
            <div class="method-value">{{ $payment->notes }}</div>
        </div>
    </div>
</div>
@endif

<!-- Payment Terms Section -->
<div class="info-box" style="margin-top: 15px; background: #fef3c7; border-left: 3px solid #d97706;">
    <div class="info-title" style="color: #d97706;">PAYMENT TERMS & CONDITIONS</div>
    <ul>
        <li>All payments are non-refundable once the milestone is completed.</li>
        <li>Payment is due within 7 days of invoice date.</li>
        <li>A late fee of 5% will be applied for payments delayed beyond the due date.</li>
        <li>Project work will only commence after the initial payment is received.</li>
        <li>All disputes shall be resolved under the jurisdiction of Lahore Courts.</li>
        <li>This receipt is system generated and does not require a physical signature.</li>
    </ul>
</div>

<!-- Footer -->
<html_MyFooter>
    <div class="page-footer">
        <div>www.webinventorz.com | info@webinventorz.com | +92 300 123 4567</div>
        <div style="margin-top: 3px;">© {{ date('Y') }} WebInventorz - All Rights Reserved</div>
    </div>
</html_MyFooter>

</body>
</html>