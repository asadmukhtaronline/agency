<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lead Report - {{ $lead->company_name }}</title>
    <style>
        @page {
            margin: 20px;
            header: html_MyHeader;
            footer: html_MyFooter;
        }
        /* Prevent extra blank pages */
html, body {
    margin: 0;
    padding: 0;
    height: auto;
}

.page-break {
    page-break-after: avoid;
    page-break-before: avoid;
    break-inside: avoid;
}

* {
    page-break-inside: avoid;
    page-break-after: avoid;
    page-break-before: avoid;
}

/* Only allow breaks where explicitly needed */
.gallery-section, .requirements-section, .suggestions-section {
    page-break-inside: avoid;
    break-inside: avoid;
}
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        
        /* Prevent page break inside boxes */
        .info-box, .client-box, .requirements-section, .suggestions-section, .gallery-section {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        
        /* Header */
        html_MyHeader {
            position: running(header);
        }
        
        .page-header {
            border-bottom: 3px solid #FE171B;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        
        .logo-section {
            float: left;
            width: 50%;
        }
        
        .logo-section img {
            max-height: 45px;
            width: auto;
        }
        
        .company-info {
            float: right;
            width: 45%;
            text-align: right;
            font-size: 9px;
            color: #64748b;
            line-height: 1.4;
        }
        
        .company-info strong {
            color: #1e293b;
            font-size: 10px;
            font-weight: 700;
        }
        
        .clearfix {
            clear: both;
        }
        
        /* Document Title */
        .doc-title {
            text-align: center;
            margin: 20px 0;
            page-break-after: avoid;
        }
        
        .doc-title h1 {
            font-size: 22px;
            font-weight: 800;
            color: #FE171B;
            letter-spacing: 2px;
            margin: 0;
            padding: 8px 20px;
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
            display: inline-block;
            background: #fef2f2;
        }
        
        /* Two Column Layout */
        .two-columns {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .col-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 15px;
        }
        
        .col-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 15px;
        }
        
        /* Info Boxes */
        .info-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .info-title {
            font-size: 11px;
            font-weight: 700;
            color: #FE171B;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #FE171B;
            padding-bottom: 4px;
            display: inline-block;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 100px;
            font-weight: 600;
            color: #475569;
            padding: 4px 0;
            font-size: 10px;
        }
        
        .info-value {
            display: table-cell;
            color: #1e293b;
            padding: 4px 0;
            font-size: 10px;
            font-weight: 500;
        }
        
        /* Client Box */
        .client-box {
            background: #fef2f2;
            border-left: 4px solid #FE171B;
            border-radius: 8px;
            padding: 12px;
            page-break-inside: avoid;
        }
        
        .client-box h3 {
            font-size: 11px;
            font-weight: 700;
            color: #FE171B;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .client-details {
            font-size: 10px;
            line-height: 1.6;
            color: #334155;
        }
        
        .client-details strong {
            color: #1e293b;
        }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 15px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-new { background: #dbeafe; color: #1e40af; }
        .status-contacted { background: #cffafe; color: #0891b2; }
        .status-meeting { background: #fef3c7; color: #d97706; }
        .status-proposal { background: #e2e8f0; color: #475569; }
        .status-negotiation { background: #e0e7ff; color: #4338ca; }
        .status-won { background: #dcfce7; color: #15803d; }
        .status-lost { background: #fee2e2; color: #dc2626; }
        
        /* Priority Badge */
        .priority-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 15px;
            font-size: 9px;
            font-weight: 600;
        }
        .priority-high { background: #fee2e2; color: #dc2626; }
        .priority-medium { background: #fef3c7; color: #d97706; }
        .priority-low { background: #dcfce7; color: #15803d; }
        
        /* Section Divider */
        .section-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 15px 0;
            page-break-after: avoid;
        }
        
        /* Requirements Section */
        .requirements-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .requirements-heading {
            font-size: 12px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .requirements-content {
            font-size: 10px;
            line-height: 1.5;
            color: #334155;
            background: #fef2f2;
            padding: 10px;
            border-radius: 6px;
            word-wrap: break-word;
        }
        
        /* Suggestions Section */
        .suggestions-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        
        .suggestions-heading {
            font-size: 12px;
            font-weight: 700;
            color: #16a34a;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .suggestions-content {
            font-size: 10px;
            line-height: 1.5;
            color: #334155;
            background: #f0fdf4;
            padding: 10px;
            border-radius: 6px;
            word-wrap: break-word;
        }
        
        /* Gallery Section - 2 per row */
                /* Gallery Section - 2 per row */
        .gallery-section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .gallery-heading {
            font-size: 11px;
            font-weight: 700;
            color: #FE171B;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #FE171B;
            padding-bottom: 4px;
            display: inline-block;
        }

        .gallery-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .gallery-item {
            width: calc(98% - 7.5px);  /* Changed to 50% for 2 per row */
            height: auto;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* This is the key - auto height for images */
        .gallery-item img {
            width: 100%;
            height: auto;  /* Auto height maintains aspect ratio */
            display: block;
            object-fit: contain;
        }

        .no-image {
            padding: 40px;
            text-align: center;
            color: #94a3b8;
            font-size: 10px;
        }
        
        /* Footer */
        html_MyFooter {
            position: running(footer);
        }
        
        .page-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
        }
        
        .footer-note {
            margin-top: 5px;
            font-style: italic;
        }
        
        /* Social Links */
        .social-links {
            margin-top: 8px;
        }
        
        .social-link {
            display: block;
            font-size: 9px;
            color: #3b82f6;
            text-decoration: none;
            margin-bottom: 4px;
            word-wrap: break-word;
        }
        
        /* Keep together */
        .keep-together {
            page-break-inside: avoid;
            break-inside: avoid;
        }
    </style>
</head>
<body>

<!-- Header -->
<html_MyHeader>
    <div class="page-header">
        <div class="logo-section">
            <img src="{{ public_path('logo.png') }}" alt="Logo" height="40">
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
    <h1>CLIENT LEAD REPORT</h1>
</div>

<!-- Two Columns -->
<div class="two-columns keep-together">
    <!-- Left Column -->
    <div class="col-left">
        <div class="info-box">
            <div class="info-title">LEAD INFORMATION</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Lead ID:</div>
                    <div class="info-value"><strong>#{{ $lead->id }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Company Name:</div>
                    <div class="info-value"><strong>{{ $lead->company_name }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status:</div>
                    <div class="info-value">
                        <span class="status-badge status-{{ $lead->status }}">
                            {{ ucfirst($lead->status) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Priority:</div>
                    <div class="info-value">
                        <span class="priority-badge priority-{{ $lead->priority }}">
                            {{ ucfirst($lead->priority) }}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Created Date:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($lead->created_at)->format('F d, Y') }}</div>
                </div>
            </div>
        </div>
        
        <div class="info-box">
            <div class="info-title">BUSINESS INFORMATION</div>
            <div class="info-grid">
                @if($lead->clientType)
                <div class="info-row">
                    <div class="info-label">Industry:</div>
                    <div class="info-value">{{ $lead->clientType->name }}</div>
                </div>
                @endif
                @if($lead->workType)
                <div class="info-row">
                    <div class="info-label">Service Required:</div>
                    <div class="info-value">{{ $lead->workType->name }}</div>
                </div>
                @endif
                @if($lead->source)
                <div class="info-row">
                    <div class="info-label">Source:</div>
                    <div class="info-value">{{ ucfirst($lead->source) }}</div>
                </div>
                @endif
                @if($lead->country)
                <div class="info-row">
                    <div class="info-label">Country:</div>
                    <div class="info-value">{{ $lead->country->name }}</div>
                </div>
                @endif
                @if($lead->city)
                <div class="info-row">
                    <div class="info-label">City:</div>
                    <div class="info-value">{{ $lead->city }}</div>
                </div>
                @endif
            </div>
        </div>
        
        <div class="info-box">
            <div class="info-title">CONTACT INFORMATION</div>
            <div class="info-grid">
                @if($lead->contact_person)
                <div class="info-row">
                    <div class="info-label">Contact Person:</div>
                    <div class="info-value">{{ $lead->contact_person }}</div>
                </div>
                @endif
                @if($lead->email)
                <div class="info-row">
                    <div class="info-label">Email:</div>
                    <div class="info-value">{{ $lead->email }}</div>
                </div>
                @endif
                @if($lead->phone)
                <div class="info-row">
                    <div class="info-label">Phone:</div>
                    <div class="info-value">{{ $lead->phone }}</div>
                </div>
                @endif
                @if($lead->whatsapp_number)
                <div class="info-row">
                    <div class="info-label">WhatsApp:</div>
                    <div class="info-value">{{ $lead->whatsapp_number }}</div>
                </div>
                @endif
                @if($lead->website)
                <div class="info-row">
                    <div class="info-label">Website:</div>
                    <div class="info-value">{{ $lead->website }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-right">
        <div class="client-box">
            <h3>CLIENT SUMMARY</h3>
            <div class="client-details">
                <strong>Company:</strong> {{ $lead->company_name }}<br>
                @if($lead->contact_person)
                    <strong>Contact:</strong> {{ $lead->contact_person }}<br>
                @endif
                @if($lead->email)
                    <strong>Email:</strong> {{ $lead->email }}<br>
                @endif
                @if($lead->phone)
                    <strong>Phone:</strong> {{ $lead->phone }}<br>
                @endif
                @if($lead->workType)
                    <strong>Service Required:</strong> {{ $lead->workType->name }}<br>
                @endif
                @if($lead->clientType)
                    <strong>Industry:</strong> {{ $lead->clientType->name }}<br>
                @endif
                <strong>Lead Status:</strong> {{ ucfirst($lead->status) }}<br>
                <strong>Priority:</strong> {{ ucfirst($lead->priority) }}
            </div>
        </div>
        
        <!-- Social Links -->
        @if($lead->facebook || $lead->instagram || $lead->linkedin || $lead->twitter)
        <div class="info-box" style="margin-top: 15px;">
            <div class="info-title">SOCIAL PROFILES</div>
            <div class="social-links">
                @if($lead->facebook) 
                    <div class="social-link">Facebook: {{ $lead->facebook }}</div>
                @endif
                @if($lead->instagram) 
                    <div class="social-link">Instagram: {{ $lead->instagram }}</div>
                @endif
                @if($lead->linkedin) 
                    <div class="social-link">LinkedIn: {{ $lead->linkedin }}</div>
                @endif
                @if($lead->twitter) 
                    <div class="social-link">Twitter: {{ $lead->twitter }}</div>
                @endif
            </div>
        </div>
        @endif
        
        <!-- Timeline -->
        <div class="info-box" style="margin-top: 15px;">
            <div class="info-title">IMPORTANT DATES</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Lead Created:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($lead->created_at)->format('F d, Y') }}</div>
                </div>
                @if($lead->next_followup)
                <div class="info-row">
                    <div class="info-label">Follow-up Date:</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($lead->next_followup)->format('F d, Y') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Section Divider -->
<div class="section-divider"></div>

<!-- Requirements / Problems Section -->
@if($lead->requirements)
<div class="requirements-section keep-together">
    <div class="requirements-heading">CLIENT REQUIREMENTS / PROBLEMS</div>
    <div class="requirements-content">
        {!! $lead->requirements !!}
    </div>
</div>
@endif
<!-- Suggestions Section -->
@if($lead->notes)
<div class="suggestions-section keep-together">
    <div class="suggestions-heading">SUGGESTIONS & RECOMMENDATIONS</div>
    <div class="suggestions-content">
        {!! $lead->notes !!}
    </div>
</div>
@endif

<!-- Screenshots Gallery - 2 per row -->
<!-- Screenshots Gallery - 2 per row -->
<!-- Screenshots Gallery - Base64 with absolute path -->
<!-- Screenshots Gallery -->
@php
    $screenshots = json_decode($lead->screenshots, true) ?? [];
    $chunks = array_chunk($screenshots, 2);
@endphp
@if(!empty($screenshots))
<div class="gallery-section keep-together">
    <div class="gallery-heading">PROJECT SCREENSHOTS ({{ count($screenshots) }} images)</div>
    @foreach($chunks as $chunk)
        <div class="gallery-grid">
            @foreach($chunk as $screenshot)
            <div class="gallery-item">
                @php
                    $imageData = null;
                    $cleanPath = str_replace('\\', '/', $screenshot);
                    
                    // Try different storage paths
                    $possiblePaths = [
                        storage_path('app/public/' . $cleanPath),
                        public_path('storage/' . $cleanPath),
                        storage_path('app/public/client-screenshots/' . basename($cleanPath)),
                        public_path('storage/client-screenshots/' . basename($cleanPath)),
                        base_path('public/storage/' . $cleanPath),
                        base_path('storage/app/public/' . $cleanPath)
                    ];
                    
                    foreach($possiblePaths as $path) {
                        if(file_exists($path)) {
                            try {
                                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                $mimeType = 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension);
                                $fileData = file_get_contents($path);
                                if($fileData !== false) {
                                    $imageData = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
                                    break;
                                }
                            } catch(Exception $e) {
                                continue;
                            }
                        }
                    }
                @endphp
                
                @if($imageData)
                    <img src="{{ $imageData }}" alt="Screenshot" class="screenshot-image">
                @else
                    <div class="no-image">
                        Image not available: {{ basename($cleanPath) }}
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    @endforeach
</div>
@endif

<!-- Footer -->
<html_MyFooter>
    <div class="page-footer">
        <div>WebInventorz.com | info@webinventorz.com | +92 300 123 4567</div>
        <div class="footer-note">This is a system generated report - For business use only</div>
    </div>
</html_MyFooter>

</body>
</html>