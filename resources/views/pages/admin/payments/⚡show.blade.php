<?php

use Livewire\Component;
use App\Models\ProjectPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public $payment;
    
    public function mount($id)
    {
        $this->payment = ProjectPayment::with(['project.client', 'milestone', 'paymentGateway', 'creator'])->findOrFail($id);
    }
    
    public function downloadInvoice()
    {
        $payment = $this->payment;
        
        if ($payment->invoice_pdf && Storage::disk('public')->exists($payment->invoice_pdf)) {
            return Storage::disk('public')->download($payment->invoice_pdf, 'invoice-' . $payment->payment_number . '.pdf');
        } else {
            // Generate new invoice if not exists
            $pdf = Pdf::loadView('pdf.payment-invoice', [
                'payment' => $payment,
                'project' => $payment->project,
                'client' => $payment->project->client
            ]);
            
            $filename = 'invoice-' . $payment->payment_number . '.pdf';
            $filepath = 'invoices/' . $filename;
            Storage::disk('public')->put($filepath, $pdf->output());
            
            $payment->update(['invoice_pdf' => $filepath]);
            
            return Storage::disk('public')->download($filepath, $filename);
        }
    }
    
    public function deletePayment()
    {
        $payment = $this->payment;
        
        // Delete invoice PDF if exists
        if ($payment->invoice_pdf && Storage::disk('public')->exists($payment->invoice_pdf)) {
            Storage::disk('public')->delete($payment->invoice_pdf);
        }
        
        $project = $payment->project;
        $payment->delete();
        
        // Update project payment status
        $totalPaid = $project->payments()->sum('amount');
        $project->update([
            'paid_amount' => $totalPaid,
            'due_amount' => $project->total_amount - $totalPaid,
            'payment_status' => $totalPaid >= $project->total_amount ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid')
        ]);
        
        session()->flash('message', 'Payment deleted successfully!');
        return redirect()->route('admin.payments.index');
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
            <h2 class="fw-bold mb-0">Payment Details</h2>
            <p class="text-muted">View payment information</p>
        </div>
        <div class="btn-group">
            <button wire:click="downloadInvoice" class="btn btn-danger rounded-3 me-2">
                <i class="fas fa-file-pdf me-2"></i> Download Invoice
            </button>
            <button wire:click="deletePayment" class="btn btn-danger rounded-3 me-2" onclick="return confirm('Are you sure you want to delete this payment?')">
                <i class="fas fa-trash-alt me-2"></i> Delete Payment
            </button>
            <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Payment Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i> Payment Information</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Payment #:</strong></td>
                            <td><code>{{ $payment->payment_number }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Amount:</strong></td>
                            <td><strong class="text-success">${{ number_format($payment->amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Payment Date:</strong></td>
                            <td>{{ $payment->payment_date->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Method:</strong></td>
                            <td>
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
                            </td>
                        </tr>
                        @if($payment->transaction_id)
                        <tr>
                            <td><strong>Transaction ID:</strong></td>
                            <td><code>{{ $payment->transaction_id }}</code></td>
                        </tr>
                        @endif
                        @if($payment->receipt_number)
                        <tr>
                            <td><strong>Receipt #:</strong></td>
                            <td>{{ $payment->receipt_number }}</td>
                        </tr>
                        @endif
                        @if($payment->payment_gateway_id)
                        <tr>
                            <td><strong>Payment Gateway:</strong></td>
                            <td>{{ $payment->paymentGateway->name ?? 'N/A' }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                @php
                                    $statusColors = ['pending' => 'warning', 'completed' => 'success', 'failed' => 'danger'];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$payment->status] }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created By:</strong></td>
                            <td>{{ $payment->creator->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created At:</strong></td>
                            <td>{{ $payment->created_at->format('F d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Project Information Card -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i> Project Information</h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless">
                        <tr>
                            <td width="150"><strong>Project:</strong></td>
                            <td><strong>{{ $payment->project->name }}</strong></td>
                        </tr>
                        <tr>
                            <td><strong>Project Code:</strong></td>
                            <td><code>{{ $payment->project->project_code }}</code></td>
                        </tr>
                        <tr>
                            <td><strong>Client:</strong></td>
                            <td>{{ $payment->project->client->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Client Email:</strong></td>
                            <td>{{ $payment->project->client->email }}</td>
                        </tr>
                        @if($payment->milestone)
                        <tr>
                            <td><strong>Milestone:</strong></td>
                            <td>{{ $payment->milestone->milestone_name }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Total Project Amount:</strong></td>
                            <td>${{ number_format($payment->project->total_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Paid:</strong></td>
                            <td class="text-success">${{ number_format($payment->project->paid_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Due Amount:</strong></td>
                            <td class="text-danger">${{ number_format($payment->project->due_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td><strong>Project Status:</strong></td>
                            <td>
                                @php
                                    $projectStatusColors = [
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'on_hold' => 'secondary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $projectStatusColors[$payment->project->status] }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->project->status)) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notes Card -->
    @if($payment->notes)
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i> Notes</h5>
                    </div>
                    <div class="card-body p-4">
                        <p>{{ $payment->notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Invoice Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-file-pdf me-2"></i> Invoice</h5>
                </div>
                <div class="card-body p-4">
                    @if($payment->invoice_pdf)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Invoice PDF is available.
                            <button wire:click="downloadInvoice" class="btn btn-sm btn-danger ms-3">
                                <i class="fas fa-download me-1"></i> Download Invoice
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No invoice generated yet.
                            <button wire:click="downloadInvoice" class="btn btn-sm btn-primary ms-3">
                                <i class="fas fa-file-pdf me-1"></i> Generate & Download
                            </button>
                        </div>
                    @endif
                </div>
            </div>
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
    .table-borderless td, .table-borderless th {
        padding: 8px 0;
    }
</style>