<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Estimate #{{ $estimate->estimate_number }}</title>
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
            max-height: 40px;
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
            font-size: 28px;
            font-weight: 800;
            color: #FE171B;
            letter-spacing: 4px;
            margin: 0;
            padding: 8px 25px;
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            background: linear-gradient(135deg, #fff 0%, #fef2f2 100%);
        }
        
        /* Two Columns */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 15px;
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
            border-radius: 10px;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
        }
        
        .info-title {
            font-size: 9px;
            font-weight: 700;
            color: #FE171B;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1.5px solid #FE171B;
            padding-bottom: 4px;
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
            padding: 4px 0;
            font-size: 9px;
        }
        
        .info-value {
            display: table-cell;
            color: #1e293b;
            padding: 4px 0;
            font-size: 9px;
            font-weight: 500;
        }
        
        /* Client Box */
        .client-box {
            background: linear-gradient(135deg, #fef2f2 0%, #ffffff 100%);
            border-left: 3px solid #FE171B;
            border-radius: 10px;
            padding: 10px 12px;
        }
        
        .client-box h3 {
            font-size: 9px;
            font-weight: 700;
            color: #FE171B;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }
        
        .client-details {
            font-size: 9px;
            line-height: 1.5;
            color: #334155;
        }
        
        .client-details strong {
            color: #1e293b;
        }
        
        /* Estimate Table */
        .estimate-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .estimate-table th {
            background: #1e293b;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .estimate-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            background: white;
        }
        
        .estimate-table tr:last-child td {
            border-bottom: none;
        }
        
        /* Totals Box */
        .totals-box {
            width: 280px;
            margin-left: auto;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            background: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .totals-table td {
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .totals-table tr:last-child td {
            border-bottom: none;
        }
        
        .grand-total {
            background: #FE171B;
            color: white;
            font-weight: 700;
        }
        
        .grand-total td {
            padding: 8px 12px;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-draft { background: #fef3c7; color: #d97706; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #dcfce7; color: #15803d; }
        .status-rejected { background: #fee2e2; color: #dc2626; }
        .status-expired { background: #e2e8f0; color: #475569; }
        
        /* Project Box */
        .project-box {
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 15px;
        }
        
        .project-title {
            font-size: 10px;
            font-weight: 700;
            color: #15803d;
            margin-bottom: 5px;
        }
        
        /* Disclaimer Box */
        .disclaimer-box {
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            border-radius: 10px;
            padding: 10px 12px;
            margin-top: 15px;
        }
        
        .disclaimer-title {
            font-size: 9px;
            font-weight: 700;
            color: #d97706;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .disclaimer-text {
            font-size: 8px;
            color: #78350f;
            line-height: 1.4;
        }
        
        /* Notes & Terms Box */
        .notes-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 10px 12px;
            margin-top: 10px;
            border: 1px solid #e2e8f0;
        }
        
        .notes-title {
            font-size: 9px;
            font-weight: 700;
            color: #FE171B;
            margin-bottom: 5px;
        }
        
        .notes-content {
            font-size: 8px;
            color: #475569;
            line-height: 1.4;
        }
        
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
            background: linear-gradient(90deg, #FE171B, #e2e8f0, #FE171B);
            margin: 12px 0;
        }
        
        /* Text utilities */
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .fw-bold {
            font-weight: 700;
        }
        
        .text-primary {
            color: #FE171B;
        }
    </style>
</head>
<body>

<!-- Header -->
<html_MyHeader>
    <div class="page-header">
        <div class="logo-section">
            <img src="{{ public_path('logo.png') }}" alt="WebInventorz" height="35">
        </div>
        <div class="company-info">
            <strong>WebInventorz.com</strong><br>
            info@webinventorz.com | +92 300 123 4567<br>
            Lahore, Pakistan
        </div>
        <div class="clearfix"></div>
    </div>
</html_MyHeader>

<!-- Document Title -->
<div class="doc-title">
    <h1>ESTIMATE</h1>
</div>

<!-- Two Columns: Estimate Info & Client Info -->
<div class="two-columns">
    <!-- Left Column - Estimate Information -->
    <div class="col-left">
        <div class="info-box">
            <div class="info-title">ESTIMATE DETAILS</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Estimate Number:</div>
                    <div class="info-value"><strong>{{ $estimate->estimate_number }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Created Date:</div>
                    <div class="info-value">{{ $estimate->created_at->format('F d, Y') }}</div>
                </div>
                @if($estimate->valid_until)
                <div class="info-row">
                    <div class="info-label">Valid Until:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($estimate->valid_until)->format('F d, Y') }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $estimate->status }}">
                            {{ ucfirst($estimate->status) }}
                        </span>
                    </div>
                </div>
                @if($estimate->service_type_id)
                <div class="info-row">
                    <div class="info-label">Service Type:</div>
                    <div class="info-value">{{ $estimate->serviceType->name ?? 'N/A' }}</div>
                </div>
                @endif
                @if($estimate->pricing_plan_id)
                <div class="info-row">
                    <div class="info-label">Package:</div>
                    <div class="info-value">{{ $estimate->pricingPlan->name ?? 'N/A' }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Right Column - Client Information -->
    <div class="col-right">
        <div class="client-box">
            <h3>BILL TO</h3>
            <div class="client-details">
                <strong>{{ $client->name }}</strong><br>
                @if($client->company)
                    {{ $client->company }}<br>
                @endif
                @if($client->email)
                    {{ $client->email }}<br>
                @endif
                @if($client->phone)
                    {{ $client->phone }}<br>
                @endif
                @if($client->address)
                    {{ $client->address }}<br>
                @endif
                @if($client->city || $client->state)
                    {{ $client->city }} {{ $client->state }}<br>
                @endif
                @if($client->country)
                    {{ $client->country->name }}
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Section Divider -->
<div class="section-divider"></div>

<!-- Project Details -->
@if($estimate->title)
<div class="project-box">
    <div class="project-title">📋 PROJECT DETAILS</div>
    <div class="client-details" style="margin-top: 5px;">
        <strong>{{ $estimate->title }}</strong>
        @if($estimate->description)
            <br><span style="font-size: 8px; color: #475569;">{{ $estimate->description }}</span>
        @endif
    </div>
</div>
@endif

<!-- Estimate Items Table -->
<table class="estimate-table">
    <thead>
        <tr>
            <th style="width: 5%">#</th>
            <th style="width: 40%">Item</th>
            <th style="width: 20%">Description</th>
            <th style="width: 10%">Qty</th>
            <th style="width: 12%">Unit Price</th>
            <th style="width: 13%">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td
            <td><strong>{{ $item->item_name }}</strong></td
            <td>{{ $item->description ?? '—' }}</td
            <td>{{ $item->quantity }}</td
            <td>{{ $country->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td
            <td>{{ $country->currency_symbol }}{{ number_format($item->total, 2) }}</td
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Totals Section -->
<div class="totals-box">
    <table class="totals-table">
        <tr>
            <td style="width: 60%">Subtotal:</td>
            <td style="width: 40%" class="text-right">{{ $country->currency_symbol }}{{ number_format($estimate->subtotal, 2) }}</td>
        </tr>
        @if($estimate->tax_rate > 0)
        <tr>
            <td>Tax ({{ $estimate->tax_rate }}%):</td>
            <td class="text-right">{{ $country->currency_symbol }}{{ number_format($estimate->tax_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td><strong>TOTAL:</strong></td>
            <td class="text-right"><strong>{{ $country->currency_symbol }}{{ number_format($estimate->total, 2) }}</strong></td>
        </tr>
    </table>
</div>

<!-- Section Divider -->
<div class="section-divider"></div>

<!-- Notes Section -->
@if($estimate->notes)
<div class="notes-box">
    <div class="notes-title">📝 NOTES</div>
    <div class="notes-content">{{ $estimate->notes }}</div>
</div>
@endif

<!-- Terms & Conditions -->
@if($estimate->terms_conditions)
<div class="notes-box" style="margin-top: 8px;">
    <div class="notes-title">⚖️ TERMS & CONDITIONS</div>
    <div class="notes-content">{{ $estimate->terms_conditions }}</div>
</div>
@endif

<!-- Disclaimer -->
<div class="disclaimer-box">
    <div class="disclaimer-title">⚠️ IMPORTANT NOTE</div>
    <div class="disclaimer-text">
        This is an estimate only, not an invoice. Prices are subject to change. 
        This estimate is valid until {{ $estimate->valid_until ? \Carbon\Carbon::parse($estimate->valid_until)->format('F d, Y') : '30 days from issue date' }}.
        A formal invoice will be issued upon acceptance of this estimate.
    </div>
</div>

<!-- Footer -->
<html_MyFooter>
    <div class="page-footer">
        <div>www.webinventorz.com | info@webinventorz.com | +92 300 123 4567</div>
        <div style="margin-top: 3px;">© {{ date('Y') }} WebInventorz - All Rights Reserved</div>
        <div style="margin-top: 3px; font-style: italic;">This is a system generated estimate - No signature required</div>
    </div>
</html_MyFooter>

</body>
</html>