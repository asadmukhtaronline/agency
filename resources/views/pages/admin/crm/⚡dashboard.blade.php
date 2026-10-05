<?php

use Livewire\Component;
use App\Models\ClientLead;
use App\Models\Communication;
use App\Models\ClientType;
use App\Models\WorkType;

new class extends Component
{
    public $date_range = 'week'; // week, month, year
    
    // Dashboard Stats
    public $total_leads = 0;
    public $new_leads = 0;
    public $contacted_leads = 0;
    public $won_leads = 0;
    public $lost_leads = 0;
    public $pending_followups = 0;
    public $total_communications = 0;
    public $positive_responses = 0;
    
    // Chart Data
    public $leads_by_status = [];
    public $leads_by_work_type = [];
    public $leads_by_client_type = [];
    public $communications_by_type = [];
    public $weekly_communications = [];
    public $conversion_rate = 0;
    
    // Recent Data
    public $recent_leads = [];
    public $recent_communications = [];
    public $today_followups = [];
    
    public function mount()
    {
        $this->loadDashboardData();
    }
    
    public function updatedDateRange()
    {
        $this->loadDashboardData();
    }
    
    public function loadDashboardData()
{
    // Basic Stats
    $this->total_leads = ClientLead::count();
    $this->new_leads = ClientLead::where('status', 'new')->count();
    $this->contacted_leads = ClientLead::whereIn('status', ['contacted', 'meeting', 'proposal', 'negotiation'])->count();
    $this->won_leads = ClientLead::where('status', 'won')->count();
    $this->lost_leads = ClientLead::where('status', 'lost')->count();
    $this->total_communications = Communication::count();
    $this->positive_responses = Communication::where('client_response', 'interested')->count();
    $this->pending_followups = Communication::whereNotNull('next_followup')
        ->where('next_followup', '>=', now())
        ->where('next_followup', '<=', now()->addDays(7))
        ->count();
    
    // Conversion Rate
    $totalContacted = $this->contacted_leads + $this->won_leads;
    $this->conversion_rate = $totalContacted > 0 ? round(($this->won_leads / $totalContacted) * 100) : 0;
    
    // Leads by Status
    $this->leads_by_status = [
        'New' => ClientLead::where('status', 'new')->count(),
        'Contacted' => ClientLead::where('status', 'contacted')->count(),
        'Meeting' => ClientLead::where('status', 'meeting')->count(),
        'Proposal' => ClientLead::where('status', 'proposal')->count(),
        'Negotiation' => ClientLead::where('status', 'negotiation')->count(),
        'Won' => ClientLead::where('status', 'won')->count(),
        'Lost' => ClientLead::where('status', 'lost')->count(),
    ];
    
    // Leads by Work Type (Alternative - Direct Query)
    $this->leads_by_work_type = [];
    $workTypes = WorkType::where('is_active', true)->limit(6)->get();
    foreach ($workTypes as $type) {
        $count = ClientLead::where('work_type_id', $type->id)->count();
        if ($count > 0) {
            $this->leads_by_work_type[] = ['name' => $type->name, 'count' => $count];
        }
    }
    
    // Leads by Client Type (Alternative - Direct Query)
    $this->leads_by_client_type = [];
    $clientTypes = ClientType::where('is_active', true)->limit(6)->get();
    foreach ($clientTypes as $type) {
        $count = ClientLead::where('client_type_id', $type->id)->count();
        if ($count > 0) {
            $this->leads_by_client_type[] = ['name' => $type->name, 'count' => $count];
        }
    }
    
    // Communications by Type
    $this->communications_by_type = [
        'Calls' => Communication::where('type', 'call')->count(),
        'Emails' => Communication::where('type', 'email')->count(),
        'Meetings' => Communication::where('type', 'meeting')->count(),
        'WhatsApp' => Communication::where('type', 'whatsapp')->count(),
    ];
    
    // Weekly Communications
    $weekly = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = now()->subDays($i);
        $count = Communication::whereDate('created_at', $date)->count();
        $weekly[] = ['day' => $date->format('D'), 'count' => $count];
    }
    $this->weekly_communications = $weekly;
    
    // Recent Leads
    $this->recent_leads = ClientLead::orderBy('created_at', 'desc')->limit(5)->get();
    
    // Recent Communications
    $this->recent_communications = Communication::with('clientLead')->orderBy('created_at', 'desc')->limit(5)->get();
    
    // Today's Followups
    $this->today_followups = Communication::with('clientLead')
        ->whereDate('next_followup', now())
        ->orderBy('next_followup', 'asc')
        ->get();
}
};

?>

<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">CRM Dashboard</h2>
            <p class="text-muted">Overview of your client hunting and sales activities</p>
        </div>
        <div>
            <select wire:model.live="date_range" class="form-select rounded-3 w-auto">
                <option value="week">Last 7 Days</option>
                <option value="month">Last 30 Days</option>
                <option value="all">All Time</option>
            </select>
        </div>
    </div>
    
    <!-- Stats Cards Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Leads</span>
                            <h2 class="mb-0 fw-bold">{{ number_format($total_leads) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Won Deals</span>
                            <h2 class="mb-0 fw-bold text-success">{{ number_format($won_leads) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-trophy fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Conversion Rate</span>
                            <h2 class="mb-0 fw-bold text-info">{{ $conversion_rate }}%</h2>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Pending Follow-ups</span>
                            <h2 class="mb-0 fw-bold text-warning">{{ number_format($pending_followups) }}</h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-bell fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards Row 2 -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">New Leads</span>
                            <h4 class="mb-0 fw-bold">{{ number_format($new_leads) }}</h4>
                        </div>
                        <i class="fas fa-star fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Contacted</span>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($contacted_leads) }}</h4>
                        </div>
                        <i class="fas fa-phone fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Communications</span>
                            <h4 class="mb-0 fw-bold">{{ number_format($total_communications) }}</h4>
                        </div>
                        <i class="fas fa-comments fa-2x text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Positive Responses</span>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($positive_responses) }}</h4>
                        </div>
                        <i class="fas fa-thumbs-up fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Leads by Status Chart -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Leads by Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody>
                                @foreach($leads_by_status as $status => $count)
                                    @php
                                        $total = array_sum($leads_by_status);
                                        $percentage = $total > 0 ? round(($count / $total) * 100) : 0;
                                        $barColor = match($status) {
                                            'New' => 'primary',
                                            'Contacted' => 'info',
                                            'Meeting' => 'warning',
                                            'Proposal' => 'secondary',
                                            'Negotiation' => 'dark',
                                            'Won' => 'success',
                                            'Lost' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <tr>
                                        <td width="120">{{ $status }}</td>
                                        <td width="60">{{ number_format($count) }}</td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $barColor }}" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </td>
                                        <td width="50">{{ $percentage }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Weekly Communications Chart -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Weekly Communications</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-end justify-content-between" style="height: 200px;">
                        @foreach($weekly_communications as $data)
                            @php $height = max(5, ($data['count'] > 0 ? ($data['count'] / max(array_column($weekly_communications, 'count'))) * 100 : 5)); @endphp
                            <div class="text-center" style="flex: 1;">
                                <div class="bg-primary rounded-3 mx-1" style="height: {{ $height }}px; width: 40px; margin: 0 auto;"></div>
                                <small class="text-muted mt-2 d-block">{{ $data['day'] }}</small>
                                <strong>{{ $data['count'] }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Leads by Work Type -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Leads by Work Type</h5>
                </div>
                <div class="card-body p-4">
                    @if(count($leads_by_work_type) > 0)
                        @foreach($leads_by_work_type as $type)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between">
                                    <span>{{ $type['name'] }}</span>
                                    <span class="fw-bold">{{ $type['count'] }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    @php $max = max(array_column($leads_by_work_type, 'count')); @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $max > 0 ? ($type['count'] / $max) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No data available</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Communications by Type -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i> Communications by Type</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-phone fa-2x text-success"></i>
                                <h4 class="mb-0 mt-2">{{ number_format($communications_by_type['Calls']) }}</h4>
                                <small class="text-muted">Calls</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-envelope fa-2x text-primary"></i>
                                <h4 class="mb-0 mt-2">{{ number_format($communications_by_type['Emails']) }}</h4>
                                <small class="text-muted">Emails</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-handshake fa-2x text-warning"></i>
                                <h4 class="mb-0 mt-2">{{ number_format($communications_by_type['Meetings']) }}</h4>
                                <small class="text-muted">Meetings</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fab fa-whatsapp fa-2x text-success"></i>
                                <h4 class="mb-0 mt-2">{{ number_format($communications_by_type['WhatsApp']) }}</h4>
                                <small class="text-muted">WhatsApp</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Leads & Communications -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Recent Leads</h5>
                    <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-sm btn-link">View All →</a>
                </div>
                <div class="card-body p-0">
                    @if($recent_leads->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_leads as $lead)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $lead->company_name }}</strong>
                                        <br><small class="text-muted">{{ $lead->contact_person ?? 'No contact person' }}</small>
                                    </div>
                                    <span class="badge 
                                        @if($lead->status == 'new') bg-primary
                                        @elseif($lead->status == 'won') bg-success
                                        @elseif($lead->status == 'lost') bg-danger
                                        @else bg-info
                                        @endif">
                                        {{ ucfirst($lead->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No leads found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Communications</h5>
                    <a href="{{ route('admin.crm.communication') }}" class="btn btn-sm btn-link">View All →</a>
                </div>
                <div class="card-body p-0">
                    @if($recent_communications->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recent_communications as $comm)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $comm->clientLead->company_name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                @if($comm->type == 'call')📞 Call
                                                @elseif($comm->type == 'email')✉️ Email
                                                @elseif($comm->type == 'meeting')🤝 Meeting
                                                @else💬 WhatsApp
                                                @endif
                                                • {{ $comm->created_at->diffForHumans() }}
                                            </small>
                                        </div>
                                        @if($comm->client_response)
                                            <span class="badge 
                                                @if($comm->client_response == 'interested') bg-success
                                                @elseif($comm->client_response == 'not_interested') bg-danger
                                                @else bg-warning
                                                @endif">
                                                {{ str_replace('_', ' ', $comm->client_response) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No communications found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Today's Follow-ups -->
    @if($today_followups->count() > 0)
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-warning bg-opacity-10 rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-bell me-2"></i> Today's Follow-ups</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($today_followups as $followup)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $followup->clientLead->company_name }}</strong>
                            <br><small class="text-muted">Last: {{ $followup->type }} on {{ $followup->created_at->format('M d') }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    .list-group-item:first-child {
        border-top: none;
    }
    .list-group-item:last-child {
        border-bottom: none;
    }
    .progress {
        border-radius: 10px;
    }
    .card-header {
        border-bottom: none;
        padding: 1rem 1.5rem;
    }
</style>