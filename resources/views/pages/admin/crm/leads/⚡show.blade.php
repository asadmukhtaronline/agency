<?php

use Livewire\Component;
use App\Models\ClientLead;
use App\Models\Communication;

new class extends Component
{
    public $leadId;
    public $lead;
    public $communications;
    public $totalCommunications;
    public $lastCommunication;
    public $screenshots = [];
    
    public function mount($id)
    {
        $this->leadId = $id;
        $this->loadLead();
        $this->loadCommunications();
        $this->loadScreenshots();
    }
    
    public function loadLead()
    {
        $this->lead = ClientLead::with(['clientType', 'workType', 'country'])
            ->find($this->leadId);
        
        if (!$this->lead) {
            session()->flash('error', 'Lead not found!');
            return redirect()->route('admin.crm.leads.index');
        }
    }
    
    public function loadCommunications()
    {
        $this->communications = Communication::where('client_lead_id', $this->leadId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $this->totalCommunications = $this->communications->count();
        $this->lastCommunication = $this->communications->first();
    }
    
    public function loadScreenshots()
    {
        if ($this->lead && $this->lead->screenshots) {
            $this->screenshots = json_decode($this->lead->screenshots, true) ?? [];
        }
    }
    
    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'new' => 'bg-primary',
            'contacted' => 'bg-info',
            'meeting' => 'bg-warning',
            'proposal' => 'bg-secondary',
            'negotiation' => 'bg-dark',
            'won' => 'bg-success',
            'lost' => 'bg-danger',
            default => 'bg-secondary'
        };
    }
    
    public function getPriorityBadgeClass($priority)
    {
        return match($priority) {
            'low' => 'bg-secondary',
            'medium' => 'bg-warning',
            'high' => 'bg-danger',
            default => 'bg-secondary'
        };
    }
    
    public function getResponseBadgeClass($response)
    {
        return match($response) {
            'interested' => 'bg-success',
            'not_interested' => 'bg-danger',
            'call_back' => 'bg-warning text-dark',
            'follow_up' => 'bg-info',
            default => 'bg-secondary'
        };
    }
    
    public function getCommunicationIcon($type)
    {
        return match($type) {
            'call' => 'fa-phone text-success',
            'email' => 'fa-envelope text-primary',
            'meeting' => 'fa-handshake text-warning',
            'whatsapp' => 'fab fa-whatsapp text-success',
            default => 'fa-comment text-secondary'
        };
    }
    public function downloadPDF()
{
    $lead = $this->lead;
    $communications = $this->communications;
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lead-details', [
        'lead' => $lead,
        'communications' => $communications,
    ]);
    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled' => true
    ]);
    
    $pdf->setPaper('A4', 'portrait');
    
    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->output();
    }, 'lead-details-' . $lead->company_name . '.pdf');
}
};

?>

<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Lead Details <i class="fa fa-spinner fa-spin" wire:loading></i> </h2>
            <p class="text-muted">Complete information and communication history</p>
        </div>
        <div>
            <button wire:click="downloadPDF" class="btn btn-sm btn-success rounded-3 me-2">
    <i class="fas fa-download me-2"></i> Download PDF
</button>
            <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-sm btn-secondary rounded-3 me-2">
                <i class="fas fa-arrow-left me-2"></i> Back to Leads
            </a>
            <a href="{{ route('admin.crm.leads.edit', $leadId) }}" class="btn btn-sm btn-info rounded-3 me-2">
                <i class="fas fa-edit me-2"></i> Edit Lead
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Lead Information Column -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Lead Information</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Basic Info -->
                    <div class="text-center mb-4">
                        @if(!empty($screenshots))
                            <img src="{{ asset('storage/' . $screenshots[0]) }}" 
                                 class="rounded-3 mb-2" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <i class="fas fa-building fa-4x text-muted"></i>
                        @endif
                        <h3 class="mt-2 mb-0">{{ $lead->company_name }}</h3>
                        <div class="mt-2">
                            <span class="badge {{ $this->getStatusBadgeClass($lead->status) }} fs-6 px-3 py-2">
                                {{ ucfirst($lead->status) }}
                            </span>
                            <span class="badge {{ $this->getPriorityBadgeClass($lead->priority) }} fs-6 px-3 py-2 ms-2">
                                @if($lead->priority == 'high') 🔴
                                @elseif($lead->priority == 'medium') 🟡
                                @else 🟢
                                @endif
                                {{ ucfirst($lead->priority) }} Priority
                            </span>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Contact Information -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-address-card text-primary me-2"></i> Contact Information</h6>
                    <table class="table table-sm table-borderless mb-3">
                        @if($lead->contact_person)
                        <tr><td width="120" class="text-muted">Contact Person:</td><td><strong>{{ $lead->contact_person }}</strong></td></tr>
                        @endif
                        @if($lead->email)
                        <tr><td class="text-muted">Email:</td><td><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></td></tr>
                        @endif
                        @if($lead->phone)
                        <tr><td class="text-muted">Phone:</td><td><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></td></tr>
                        @endif
                        @if($lead->alternative_phone)
                        <tr><td class="text-muted">Alternative:</td><td>{{ $lead->alternative_phone }}</td></tr>
                        @endif
                        @if($lead->whatsapp_number)
                        <tr><td class="text-muted">WhatsApp:</td><td><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->whatsapp_number) }}" target="_blank">{{ $lead->whatsapp_number }}</a></td></tr>
                        @endif
                        @if($lead->website)
                        <tr><td class="text-muted">Website:</td><td><a href="{{ $lead->website }}" target="_blank">{{ Str::limit($lead->website, 30) }}</a></td></tr>
                        @endif
                    </table>
                    
                    <hr>
                    
                    <!-- Business Information -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-briefcase text-primary me-2"></i> Business Information</h6>
                    <table class="table table-sm table-borderless mb-3">
                        @if($lead->clientType)
                        <tr><td width="120" class="text-muted">Client Type:</td><td><span class="badge" style="background-color: {{ $lead->clientType->color }}">{{ $lead->clientType->name }}</span></td></tr>
                        @endif
                        @if($lead->workType)
                        <tr><td class="text-muted">Work Type:</td><td><span class="badge" style="background-color: {{ $lead->workType->color }}">{{ $lead->workType->name }}</span></td></tr>
                        @endif
                        @if($lead->source)
                        <tr><td class="text-muted">Source:</td><td>{{ ucfirst($lead->source) }}</td></tr>
                        @endif
                        @if($lead->country)
                        <tr><td class="text-muted">Country:</td><td><i class="fas fa-flag"></i> {{ $lead->country->name }}</td></tr>
                        @endif
                        @if($lead->city)
                        <tr><td class="text-muted">City:</td><td>{{ $lead->city }}</td></tr>
                        @endif
                    </table>
                    
                    <hr>
                    
                    <!-- Social Media Links -->
                    @php $socialLinks = $lead->getSocialLinksAttribute(); @endphp
                    @if(!empty($socialLinks))
                        <h6 class="fw-bold mb-3"><i class="fas fa-share-alt text-primary me-2"></i> Social Media</h6>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @if($lead->facebook)
                                <a href="{{ $lead->facebook }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                            @endif
                            @if($lead->instagram)
                                <a href="{{ $lead->instagram }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-circle">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            @endif
                            @if($lead->twitter)
                                <a href="{{ $lead->twitter }}" target="_blank" class="btn btn-sm btn-outline-info rounded-circle">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            @endif
                            @if($lead->linkedin)
                                <a href="{{ $lead->linkedin }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-circle">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            @endif
                            @if($lead->youtube)
                                <a href="{{ $lead->youtube }}" target="_blank" class="btn btn-sm btn-outline-danger rounded-circle">
                                    <i class="fab fa-youtube"></i>
                                </a>
                            @endif
                            @if($lead->tiktok)
                                <a href="{{ $lead->tiktok }}" target="_blank" class="btn btn-sm btn-outline-dark rounded-circle">
                                    <i class="fab fa-tiktok"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                    
                    <hr>
                    
                    <!-- Requirements & Notes -->
                    @if($lead->requirements)
                        <h6 class="fw-bold mb-2"><i class="fas fa-clipboard-list text-primary me-2"></i> Requirements</h6>
                        <p class="text-muted mb-3">{{ $lead->requirements }}</p>
                    @endif
                    
                    @if($lead->notes)
                        <h6 class="fw-bold mb-2"><i class="fas fa-sticky-note text-primary me-2"></i> Notes</h6>
                        <p class="text-muted mb-0">{{ $lead->notes }}</p>
                    @endif
                    
                    <hr>
                    
                    <!-- Timeline -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-clock text-primary me-2"></i> Timeline</h6>
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Created:</td><td>{{ \Carbon\Carbon::parse($lead->created_at)->format('M d, Y h:i A') }}</td></tr>
                        <tr><td class="text-muted">Last Updated:</td><td>{{ \Carbon\Carbon::parse($lead->updated_at)->diffForHumans() }}</td></tr>
                        @if($lead->contacted_at)<tr><td class="text-muted">First Contact:</td><td>{{ \Carbon\Carbon::parse($lead->contacted_at)->format('M d, Y') }}</td></tr>@endif
                        @if($lead->meeting_at)<tr><td class="text-muted">Meeting:</td><td>{{ \Carbon\Carbon::parse($lead->meeting_at)->format('M d, Y') }}</td></tr>@endif
                        @if($lead->next_followup)<tr><td class="text-muted">Next Follow-up:</td><td>{{ \Carbon\Carbon::parse($lead->next_followup)->format('M d, Y') }}</td></tr>@endif
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Communications History Column -->
        <div class="col-md-7">
            <!-- Communication History Card -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i> Communication History
                        <span class="badge bg-light text-dark ms-2">{{ $totalCommunications }} records</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($totalCommunications > 0)
                        <div class="timeline p-4">
                            @foreach($communications as $comm)
                                <div class="timeline-item mb-4 pb-3 border-bottom">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="rounded-circle bg-light p-2 me-3 text-center" style="width: 45px; height: 45px;">
                                                <i class="fas {{ $this->getCommunicationIcon($comm->type) }} fa-lg"></i>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <strong class="text-capitalize">{{ $comm->type }}</strong>
                                                        @if($comm->direction == 'outgoing')
                                                            <span class="badge bg-info ms-1">Outgoing</span>
                                                        @else
                                                            <span class="badge bg-secondary ms-1">Incoming</span>
                                                        @endif
                                                        @if($comm->duration)
                                                            <span class="badge bg-light text-dark ms-1"><i class="fas fa-clock"></i> {{ $comm->duration }} min</span>
                                                        @endif
                                                    </h6>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        {{ \Carbon\Carbon::parse($comm->created_at)->format('M d, Y h:i A') }}
                                                    </small>
                                                </div>
                                                @if($comm->client_response)
                                                    <span class="badge {{ $this->getResponseBadgeClass($comm->client_response) }} px-3 py-2">
                                                        @if($comm->client_response == 'interested') ✅
                                                        @elseif($comm->client_response == 'not_interested') ❌
                                                        @elseif($comm->client_response == 'call_back') 📞
                                                        @else 🔄
                                                        @endif
                                                        {{ ucfirst(str_replace('_', ' ', $comm->client_response)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($comm->subject)
                                                <div class="mt-2 p-2 bg-light rounded-3">
                                                    <strong><i class="fas fa-heading me-1"></i> Subject:</strong>
                                                    <span>{{ $comm->subject }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($comm->notes)
                                                <div class="mt-2">
                                                    <i class="fas fa-comment text-muted me-1"></i>
                                                    <span class="text-muted">{{ $comm->notes }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($comm->next_followup)
                                                <div class="mt-2 pt-1">
                                                    <i class="fas fa-bell text-warning me-1"></i>
                                                    <small class="text-muted">
                                                        Next follow-up: 
                                                        <strong>{{ \Carbon\Carbon::parse($comm->next_followup)->format('M d, Y') }}</strong>
                                                        @if(\Carbon\Carbon::parse($comm->next_followup)->isToday())
                                                            <span class="badge bg-warning text-dark ms-1">Today</span>
                                                        @elseif(\Carbon\Carbon::parse($comm->next_followup)->isPast())
                                                            <span class="badge bg-danger ms-1">Overdue</span>
                                                        @endif
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No communications yet</h5>
                            <p class="text-muted">Start recording communications with this lead</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Summary Stats Card -->
            @if($totalCommunications > 0)
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-header bg-light rounded-top-4">
                        <h6 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i> Communication Summary</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="border-end">
                                    <h5 class="mb-0 text-primary">{{ $communications->where('type', 'call')->count() }}</h5>
                                    <small class="text-muted">Calls</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border-end">
                                    <h5 class="mb-0 text-info">{{ $communications->where('type', 'email')->count() }}</h5>
                                    <small class="text-muted">Emails</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="border-end">
                                    <h5 class="mb-0 text-warning">{{ $communications->where('type', 'meeting')->count() }}</h5>
                                    <small class="text-muted">Meetings</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <h5 class="mb-0 text-success">{{ $communications->where('type', 'whatsapp')->count() }}</h5>
                                <small class="text-muted">WhatsApp</small>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
                        <!-- Screenshots Gallery Section -->
            @if(!empty($screenshots))
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0">
                        <i class="fas fa-images me-2"></i> Screenshots Gallery
                        <span class="badge bg-light text-dark ms-2">{{ count($screenshots) }} images</span>
                    </h5>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        @foreach($screenshots as $index => $screenshot)
                            <div class="col-md-4 col-sm-6">
                                <div class="gallery-item position-relative">
                                    <a href="{{ asset('storage/' . $screenshot) }}" data-lightbox="lead-gallery" data-title="Screenshot {{ $index + 1 }}">
                                        <img src="{{ asset('storage/' . $screenshot) }}" 
                                             class="img-fluid rounded-3 w-100" 
                                             style="height: 150px; object-fit: cover; cursor: pointer;"
                                             alt="Screenshot {{ $index + 1 }}">
                                    </a>
                                    <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 rounded-3 d-flex align-items-center justify-content-center opacity-0 transition">
                                        <i class="fas fa-search-plus fa-2x text-white"></i>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .table-borderless td {
        padding: 6px 0;
    }
    .timeline-item:last-child {
        border-bottom: none !important;
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    .btn-outline-primary, .btn-outline-danger, .btn-outline-info, .btn-outline-dark {
        transition: transform 0.2s;
    }
    .btn-outline-primary:hover, .btn-outline-danger:hover, .btn-outline-info:hover, .btn-outline-dark:hover {
        transform: translateY(-2px);
    }
    .badge {
        font-weight: 500;
    }
    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 12px;
        cursor: pointer;
    }
    .gallery-item:hover .gallery-overlay {
        opacity: 1 !important;
    }
    .transition {
        transition: all 0.3s ease;
    }
    .opacity-0 {
        opacity: 0;
    }
</style>