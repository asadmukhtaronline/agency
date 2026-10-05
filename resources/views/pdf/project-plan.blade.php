<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Project Plan | WebInventorz</title>
    <!-- Font Awesome 6 (Free CDN) - using FA4 style classes but FA6 icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ===== RESET & GLOBAL ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 15px;
            header: html_MyHeader;
            footer: html_MyFooter;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #1e293b;
            margin: 0;
            padding: 1%;
            background: white;
        }

        /* ===== HEADER / FOOTER (running elements) ===== */
        html_MyHeader {
            position: running(header);
        }

        .page-header {
            border-bottom: 2px solid #FE171B;
            padding-bottom: 8px;
            margin-bottom: 12px;
            overflow: hidden;
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
        }

        .company-info strong {
            color: #1e293b;
            font-size: 9px;
        }

        .clearfix {
            clear: both;
        }

        /* ===== TITLE SECTION ===== */
        .doc-title {
            text-align: left;
            margin: 10px 0;
        }

        .doc-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #FE171B;
            letter-spacing: 3px;
            margin: 0;
            padding: 5px 20px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            display: inline-block;
        }

        /* ===== TWO COLUMN LAYOUT ===== */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }

        .col-left {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-right: 10px;
        }

        .col-right {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding-left: 10px;
        }

        /* ===== INFO BOX (PROJECT DETAILS) ===== */
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

        .info-title i {
            margin-right: 4px;
            font-size: 9px;
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

        /* ===== CLIENT BOX (GRADIENT) ===== */
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

        .client-box h3 i {
            margin-right: 5px;
        }

        .client-details {
            font-size: 9px;
            line-height: 1.4;
        }

        .client-details i {
            width: 14px;
            margin-right: 3px;
            color: #FE171B;
        }

        /* ===== SECTION TITLE ===== */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #FE171B;
            margin: 12px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #FE171B;
            text-transform: uppercase;
        }

        .section-title i {
            margin-right: 5px;
        }

        /* ===== MILESTONE TABLE ===== */
        .milestone-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 9px;
        }

        .milestone-table th {
            background: #1e293b;
            color: white;
            padding: 6px 5px;
            text-align: left;
            font-size: 8px;
        }

        .milestone-table td {
            padding: 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        /* ===== FINANCIAL SUMMARY TABLE ===== */
        .summary-table {
            width: 250px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 9px;
            background: #f8fafc;
            margin-bottom: 12px;
        }

        .summary-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .total-row {
            background: #FE171B;
            color: white;
        }

        .total-row td {
            padding: 6px 8px;
        }

        /* ===== DESCRIPTION BOX ===== */
        .description-box {
            background: #f8fafc;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 12px;
            border-left: 2px solid #FE171B;
        }

        .description-box p {
            margin: 0;
            font-size: 9px;
        }

        .description-box strong i {
            margin-right: 4px;
        }

        /* ===== TERMS BOX (WARNING STYLE) ===== */
        .terms-box {
            background: #fef3c7;
            border-radius: 6px;
            padding: 8px;
            margin-bottom: 12px;
            border-left: 3px solid #d97706;
        }

        .terms-box strong {
            color: #d97706;
        }

        .terms-box ul {
            margin: 5px 0 0 15px;
            padding: 0;
            font-size: 8px;
            color: #78350f;
        }

        .terms-box li {
            margin-bottom: 2px;
        }

        .terms-box i {
            margin-right: 5px;
            color: #d97706;
        }

        /* ===== BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-in_progress {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-completed {
            background: #dcfce7;
            color: #15803d;
        }

        .status-on_hold {
            background: #ffe4e6;
            color: #be123c;
        }

        .status-cancelled {
            background: #f1f5f9;
            color: #475569;
        }

        .priority-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: 600;
        }

        .priority-low {
            background: #e2e8f0;
            color: #475569;
        }

        .priority-medium {
            background: #dbeafe;
            color: #2563eb;
        }

        .priority-high {
            background: #fef3c7;
            color: #d97706;
        }

        .priority-urgent {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ===== FOOTER ===== */
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

        /* ===== ICON ALIGNMENT ===== */
        .fa, .fas, .far, .fab {
            font-size: inherit;
        }

        i.icon-mr {
            margin-right: 4px;
        }
    </style>
</head>
<body>

<!-- ==================== HEADER (running) ==================== -->
<html_MyHeader>
    <div class="page-header">
        <div class="logo-section">
            <!-- Placeholder logo: using Font Awesome building icon + text instead of missing image (graceful fallback) 
                 but keeping img tag possibility. Since logo.png might be missing in demo, we show an icon + brand -->
            <div style="display: flex; align-items: center; gap: 8px;">
                <img src="{{ public_path('logo.png') }}" alt="WebInventorz" height="40">
            </div>
        </div>
        <div class="company-info">
            <strong><i class="fas fa-globe"></i> WebInventorz.com</strong><br>
            <i class="fas fa-envelope"></i> info@webinventorz.com | <i class="fas fa-phone-alt"></i> +92 300 123 4567
        </div>
        <div class="clearfix"></div>
    </div>
</html_MyHeader>

<!-- ==================== DYNAMIC DATA (Blade placeholders - visible in Laravel) ==================== -->
<!-- 
    This document uses Laravel Blade syntax: {{ $project->name }}, $milestones, $client, etc.
    For plain HTML preview, replace with dummy values. But it's designed for backend integration.
    All icons are Font Awesome 6 (FA6) but using classic 'fa' classes.
-->

<!-- TITLE with FA icon -->
<div class="doc-title">
    <h1><i class="fas fa-tasks"></i> PROJECT PLAN</h1>
</div>

<!-- TWO COLUMNS: PROJECT INFO & CLIENT INFO (FULLY REPLACED WITH FA ICONS) -->
<div class="two-columns">
    <div class="col-left">
        <div class="info-box">
            <div class="info-title">
                <i class="fas fa-paperclip"></i> PROJECT DETAILS
            </div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-hashtag"></i> Project Code:</div>
                    <div class="info-value"><strong>{{ $project->project_code }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-diagram-project"></i> Project Name:</div>
                    <div class="info-value"><strong>{{ $project->name }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-layer-group"></i> Project Type:</div>
                    <div class="info-value">{{ ucfirst($project->project_type) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-coins"></i> Payment Type:</div>
                    <div class="info-value">{{ ucfirst(str_replace('_', ' ', $project->payment_type)) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-flag-checkered"></i> Priority:</div>
                    <div class="info-value"><span class="priority-badge priority-{{ $project->priority }}">{{ ucfirst($project->priority) }}</span></div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-calendar-alt"></i> Timeline:</div>
                    <div class="info-value">{{ $project->start_date ? $project->start_date->format('M d, Y') : 'TBD' }} - {{ $project->end_date ? $project->end_date->format('M d, Y') : 'TBD' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-chart-line"></i> Status:</div>
                    <div class="info-value"><span class="status-badge status-{{ $project->status }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-right">
        <div class="client-box">
            <h3><i class="fas fa-user-friends"></i> CLIENT INFORMATION</h3>
            <div class="client-details">
                <strong><i class="fas fa-user-circle"></i> {{ $client->name }}</strong><br>
                <i class="fas fa-envelope-open-text"></i> {{ $client->email }}<br>
                @if($client->phone) <i class="fas fa-phone-volume"></i> {{ $client->phone }}<br> @endif
                @if($client->company) <i class="fas fa-building"></i> {{ $client->company }}<br> @endif
                @if($client->address) <i class="fas fa-map-marker-alt"></i> {{ $client->address }} @endif
            </div>
        </div>
    </div>
</div>

<!-- DESCRIPTION with FA Icon -->
@if($project->description)
<div class="description-box">
    <strong style="color:#FE171B;"><i class="fas fa-file-alt"></i> Description:</strong>
    <p>{{ $project->description }}</p>
</div>
@endif

<!-- ==================== MILESTONES SECTION ==================== -->
<div class="section-title">
    <i class="fas fa-flag-checkered"></i> PROJECT MILESTONES
</div>
<table class="milestone-table">
    <thead>
        <tr>
            <th><i class="fas fa-hashtag"></i> #</th>
            <th><i class="fas fa-gavel"></i> Milestone Name</th>
            <th><i class="fas fa-dollar-sign"></i> Amount</th>
            <th><i class="fas fa-calendar-week"></i> Due Date</th>
            <th><i class="fas fa-tasks"></i> Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($milestones as $index => $milestone)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td><strong>{{ $milestone->milestone_name }}</strong></td>
            <td>${{ number_format($milestone->amount, 2) }}</td>
            <td>{{ $milestone->due_date ? $milestone->due_date->format('M d, Y') : '—' }}</td>
            <td><span class="status-badge status-{{ $milestone->status }}">{{ ucfirst($milestone->status) }}</span></td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="background:#f1f5f9;">
            <td colspan="2" style="text-align:right;font-weight:700;"><i class="fas fa-calculator"></i> Total Milestones Amount:</td>
            <td colspan="3"><strong>${{ number_format($milestones->sum('amount'), 2) }}</strong></td>
        </tr>
    </tfoot>
</table>

<!-- ==================== FINANCIAL SUMMARY ==================== -->
<div class="section-title">
    <i class="fas fa-chart-pie"></i> FINANCIAL SUMMARY
</div>
<table class="summary-table">
    <tr>
        <td style="text-align:right;"><i class="fas fa-money-bill-wave"></i> Total Amount:</td>
        <td style="text-align:right;"><strong>${{ number_format($project->total_amount, 2) }}</strong></td>
    </tr>
    <tr>
        <td style="text-align:right;"><i class="fas fa-calendar-check"></i> Payment Schedule:</td>
        <td style="text-align:right;">{{ ucfirst(str_replace('_', ' ', $project->payment_type)) }}</td>
    </tr>
    @if($project->payment_type == 'milestone_based')
    <tr>
        <td style="text-align:right;"><i class="fas fa-cubes"></i> Number of Milestones:</td>
        <td style="text-align:right;">{{ $milestones->count() }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td style="text-align:right;font-weight:700;"><i class="fas fa-receipt"></i> GRAND TOTAL:</td>
        <td style="text-align:right;font-weight:700;">${{ number_format($project->total_amount, 2) }}</td>
    </tr>
</table>

<!-- ADDITIONAL NOTES with FA -->
@if($project->notes)
<div class="description-box" style="background:#fef2f2;">
    <strong style="color:#FE171B;"><i class="fas fa-sticky-note"></i> Additional Notes:</strong>
    <p>{{ $project->notes }}</p>
</div>
@endif

<!-- ==================== TERMS & CONDITIONS (dynamic based on status, all FA icons) ==================== -->
<div class="terms-box">
    <strong><i class="fas fa-scale-balanced"></i> TERMS & CONDITIONS</strong>
    <ul>
        @if($project->status == 'pending')
            <li><i class="far fa-hourglass-half"></i> This project plan is valid for 30 days from the date of issue.</li>
            <li><i class="fas fa-check-circle"></i> Milestones are subject to client approval before proceeding.</li>
            <li><i class="fas fa-pen-ruler"></i> Any changes to the project scope may affect the cost and timeline.</li>
            <li><i class="fas fa-hand-holding-usd"></i> A 50% advance payment is required to start the project.</li>
        @elseif($project->status == 'in_progress')
            <li><i class="fas fa-sync-alt"></i> Project is currently in progress as per the agreed timeline.</li>
            <li><i class="fas fa-user-check"></i> Milestones are subject to client approval before payment.</li>
            <li><i class="fas fa-exchange-alt"></i> Scope changes will be handled through change requests.</li>
            <li><i class="fas fa-wallet"></i> Payments are due as per the milestone completion schedule.</li>
        @elseif($project->status == 'on_hold')
            <li><i class="fas fa-pause-circle"></i> Project is currently on hold as per client request.</li>
            <li><i class="fas fa-calendar-week"></i> Timeline will be adjusted once project resumes.</li>
            <li><i class="fas fa-ban"></i> No further payments are due until project resumes.</li>
        @elseif($project->status == 'completed')
            <li><i class="fas fa-check-double"></i> Project has been successfully completed.</li>
            <li><i class="fas fa-box-open"></i> Final deliverables have been handed over to the client.</li>
            <li><i class="fas fa-coins"></i> All payments have been settled as per the agreement.</li>
            <li><i class="fas fa-life-ring"></i> 30 days free support period starts from completion date.</li>
        @elseif($project->status == 'cancelled')
            <li><i class="fas fa-times-circle"></i> This project has been cancelled.</li>
            <li><i class="fas fa-hand-holding-heart"></i> Payments made are non-refundable for completed work.</li>
        @endif
        <li><i class="fas fa-lock"></i> All payments are non-refundable once a milestone is completed.</li>
        <li><i class="fas fa-clock"></i> A late fee of 5% will be applied for payments delayed beyond 7 days.</li>
        <li><i class="fas fa-gavel"></i> All disputes shall be resolved under the jurisdiction of Lahore Courts.</li>
    </ul>
</div>

<!-- ==================== FOOTER (running) ==================== -->
<html_MyFooter>
    <div class="page-footer">
        <i class="fas fa-globe"></i> www.webinventorz.com | 
        <i class="fas fa-envelope"></i> info@webinventorz.com | 
        <i class="fas fa-phone-alt"></i> +92 300 123 4567
    </div>
</html_MyFooter>
</body>
</html>