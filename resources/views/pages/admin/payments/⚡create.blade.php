<?php

use Livewire\Component;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\PaymentGateway;
use App\Models\ProjectPayment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public $project_id = '';
    public $milestone_id = '';
    public $payment_gateway_id = '';
    public $amount = '';
    public $payment_date = '';
    public $payment_method = 'cash';
    public $transaction_id = '';
    public $receipt_number = '';
    public $notes = '';
    public $status = 'completed';
    
    public $project_amount = 0;
    public $project_paid = 0;
    public $project_due = 0;
    public $milestone_amount = 0;
    
    // Payment terms
    public $payment_terms = [
        'payments_are_non_refundable' => true,
        'payment_due_days' => 7,
        'late_fee_percentage' => 5
    ];
    
    public function updatedProjectId()
    {
        $project = Project::find($this->project_id);
        if ($project) {
            $this->project_amount = $project->total_amount;
            $this->project_paid = $project->paid_amount;
            $this->project_due = $project->due_amount;
            $this->milestone_id = '';
            $this->amount = '';
        }
        $this->dispatch('refreshMilestones');
    }
    
    public function updatedMilestoneId()
    {
        if ($this->milestone_id) {
            $milestone = ProjectMilestone::find($this->milestone_id);
            if ($milestone) {
                $this->milestone_amount = $milestone->amount;
                $this->amount = $milestone->amount;
            }
        }
    }
    
    public function getProjectsProperty()
    {
        return Project::orderBy('name')->get();
    }
    
    public function getMilestonesProperty()
    {
        if ($this->project_id) {
            return ProjectMilestone::where('project_id', $this->project_id)
                ->where('payment_status', '!=', 'paid')
                ->orderBy('due_date')
                ->get();
        }
        return collect();
    }
    
    public function getPaymentGatewaysProperty()
    {
        return PaymentGateway::where('is_active', true)->orderBy('order')->get();
    }
    
    public function getPaymentMethodsProperty()
    {
        return [
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            'jazzcash' => 'JazzCash',
            'easypaisa' => 'EasyPaisa'
        ];
    }
    
    public function save()
    {
        $this->validate([
            'project_id' => 'required|exists:projects,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required',
            'status' => 'required|in:pending,completed,failed'
        ]);
        
        $project = Project::find($this->project_id);
        
        // Check if amount exceeds due amount
        if ($this->amount > $project->due_amount) {
            $this->addError('amount', 'Amount cannot exceed due amount ($' . number_format($project->due_amount, 2) . ')');
            return;
        }
        
        $payment = ProjectPayment::create([
            'project_id' => $this->project_id,
            'milestone_id' => $this->milestone_id ?: null,
            'payment_gateway_id' => $this->payment_gateway_id ?: null,
            'payment_number' => ProjectPayment::generateNumber(),
            'amount' => $this->amount,
            'payment_date' => $this->payment_date,
            'payment_method' => $this->payment_method,
            'transaction_id' => $this->transaction_id,
            'receipt_number' => $this->receipt_number,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_by' => Auth::id()
        ]);
        
        // Generate Invoice PDF with terms
        $pdf = Pdf::loadView('pdf.payment-invoice', [
            'payment' => $payment,
            'project' => $project,
            'client' => $project->client,
            'payment_terms' => $this->payment_terms
        ]);
        if($project->status == "pending" || $project->status != "completed" || $project->status == "cancelled" && $project->status == "on_hold" ){
            $project->status = "in_progress";
            $project->save();
        }
        $filename = 'invoice-' . $payment->payment_number . '.pdf';
        $filepath = 'invoices/' . $filename;
        Storage::disk('public')->put($filepath, $pdf->output());
        
        $payment->update(['invoice_pdf' => $filepath]);
        
        // Update milestone payment status if linked
        if ($this->milestone_id) {
            $milestone = ProjectMilestone::find($this->milestone_id);
            $milestonePaid = ProjectPayment::where('milestone_id', $this->milestone_id)->sum('amount');
            
            // Update payment status
            $milestone->update([
                'payment_status' => $milestonePaid >= $milestone->amount ? 'paid' : ($milestonePaid > 0 ? 'partial' : 'unpaid')
            ]);
            
            // 🔥 NEW: If milestone is fully paid, mark it as completed
            if ($milestonePaid >= $milestone->amount) {
                $milestone->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                
                // Update project progress
                $project = $milestone->project;
                $completedMilestones = $project->milestones()->where('status', 'completed')->count();
                $totalMilestones = $project->milestones()->count();
                $progress = ($totalMilestones > 0) ? round(($completedMilestones / $totalMilestones) * 100) : 0;
                $project->update(['progress' => $progress]);
            }
        }
        
        // Update project payment status
        $totalPaid = ProjectPayment::where('project_id', $this->project_id)->sum('amount');
        $project->update([
            'paid_amount' => $totalPaid,
            'due_amount' => $project->total_amount - $totalPaid,
            'payment_status' => $totalPaid >= $project->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid')
        ]);
        
        session()->flash('message', 'Payment recorded successfully! Invoice generated.');
        return redirect()->route('admin.payments.show', $payment->id);
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Record Payment</h2>
            <p class="text-muted">Record a new payment from client</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Payments
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i> Payment Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Select Project <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 @error('project_id') is-invalid @enderror" wire:model.live="project_id">
                            <option value="">-- Select Project --</option>
                            @foreach($this->projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }} ({{ $project->project_code }})</option>
                            @endforeach
                        </select>
                        @error('project_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Milestone (Optional)</label>
                        <select class="form-select rounded-3" wire:model.live="milestone_id">
                            <option value="">-- Select Milestone --</option>
                            @foreach($this->milestones as $milestone)
                                <option value="{{ $milestone->id }}">
                                    {{ $milestone->milestone_name }} - ${{ number_format($milestone->amount, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Project Payment Summary -->
                @if($project_id)
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Total Amount:</strong> ${{ number_format($project_amount, 2) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Paid Amount:</strong> ${{ number_format($project_paid, 2) }}
                            </div>
                            <div class="col-md-4">
                                <strong>Due Amount:</strong> ${{ number_format($project_due, 2) }}
                            </div>
                        </div>
                    </div>
                @endif
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control rounded-3 @error('amount') is-invalid @enderror" wire:model="amount">
                        </div>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if($milestone_id && $milestone_amount > 0)
                            <small class="text-muted">Milestone amount: ${{ number_format($milestone_amount, 2) }}</small>
                        @endif
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3 @error('payment_date') is-invalid @enderror" wire:model="payment_date" value="{{ date('Y-m-d') }}">
                        @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" wire:model="payment_method">
                            @foreach($this->paymentMethods as $key => $method)
                                <option value="{{ $key }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Payment Gateway</label>
                        <select class="form-select rounded-3" wire:model="payment_gateway_id">
                            <option value="">-- Select Gateway --</option>
                            @foreach($this->paymentGateways as $gateway)
                                <option value="{{ $gateway->id }}">
                                    @if($gateway->icon)<i class="{{ $gateway->icon }} me-1"></i>@endif
                                    {{ $gateway->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Transaction ID</label>
                        <input type="text" class="form-control rounded-3" wire:model="transaction_id">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Receipt Number</label>
                        <input type="text" class="form-control rounded-3" wire:model="receipt_number">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select rounded-3" wire:model="status">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea class="form-control rounded-3" wire:model="notes" rows="2" placeholder="Any additional notes..."></textarea>
                </div>
                
                <!-- Payment Terms Alert -->
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Payment Terms & Conditions:</strong>
                    <ul class="mb-0 mt-2" style="padding-left: 20px;">
                        <li>All payments are non-refundable once the milestone is completed.</li>
                        <li>Payment is due within 7 days of invoice date.</li>
                        <li>A late fee of 5% will be applied for payments delayed beyond the due date.</li>
                        <li>Project work will only commence after the initial payment is received.</li>
                        <li>All disputes shall be resolved under the jurisdiction of Lahore Courts.</li>
                        <li>✅ When a milestone is fully paid, it will be automatically marked as completed.</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-file-invoice me-2"></i>
                    <strong>Note:</strong> Invoice will be automatically generated after saving payment.
                </div>
                
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-5 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Record Payment
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary px-5 py-2 rounded-3">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
    }
    .form-control:focus, .form-select:focus {
        border-color: #FE171B;
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, #FE171B, #cc1215);
        border: none;
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .alert-info {
        background: #e0f2fe;
        border-color: #bae6fd;
        color: #0369a1;
    }
    .alert-warning {
        background: #fef3c7;
        border-color: #fde68a;
        color: #92400e;
    }
    .alert-warning ul {
        font-size: 12px;
    }
</style>