<?php

use Livewire\Component;
use App\Models\Estimate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public $estimate;
    
    public function mount($id)
    {
        $this->estimate = Estimate::with(['client', 'items', 'country', 'serviceType', 'pricingPlan'])->findOrFail($id);
    }
    
    // Generate and Save PDF, then Download
    public function generateAndSavePDF()
    {
        $estimate = $this->estimate;
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.estimate', [
            'estimate' => $estimate,
            'items' => $estimate->items,
            'client' => $estimate->client,
            'country' => $estimate->country
        ]);
        
        // Create filename
        $filename = 'estimate-' . $estimate->estimate_number . '-' . date('YmdHis') . '.pdf';
        $filepath = 'estimates/' . $filename;
        
        // Save PDF to storage
        Storage::disk('public')->put($filepath, $pdf->output());
        
        // Update estimate record with PDF path
        $estimate->update([
            'pdf_path' => $filepath
        ]);
        
        session()->flash('message', 'PDF generated and saved successfully!');
        
        // Refresh estimate data
        $this->estimate = Estimate::with(['client', 'items', 'country'])->find($estimate->id);
        
        // Return download response
        return response()->streamDownload(function () use ($pdf, $filename) {
            echo $pdf->output();
        }, $filename);
    }
    
    // Download Already Saved PDF
    public function downloadSavedPDF()
    {
        $estimate = $this->estimate;
        
        if ($estimate->pdf_path && Storage::disk('public')->exists($estimate->pdf_path)) {
            return Storage::disk('public')->download($estimate->pdf_path, 'estimate-' . $estimate->estimate_number . '.pdf');
        } else {
            session()->flash('error', 'PDF file not found. Please generate again.');
        }
    }
    
    // Delete Saved PDF
    public function deletePDF()
    {
        $estimate = $this->estimate;
        
        if ($estimate->pdf_path && Storage::disk('public')->exists($estimate->pdf_path)) {
            Storage::disk('public')->delete($estimate->pdf_path);
            $estimate->update(['pdf_path' => null]);
            session()->flash('message', 'PDF deleted successfully!');
            
            // Refresh estimate data
            $this->estimate = Estimate::with(['client', 'items', 'country'])->find($estimate->id);
        }
    }
    
    // Update Estimate Status
    public function updateStatus($status)
    {
        $this->estimate->update(['status' => $status]);
        session()->flash('message', 'Estimate status updated successfully!');
        $this->estimate->refresh();
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
            <h2 class="fw-bold mb-0">Estimate Details</h2>
            <p class="text-muted">View estimate #{{ $estimate->estimate_number }}</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <!-- PDF Actions -->
            @if($estimate->pdf_path)
                <button wire:click="downloadSavedPDF" class="btn btn-success rounded-3">
                    <i class="fas fa-download me-2"></i> Download PDF
                </button>
                <button wire:click="deletePDF" class="btn btn-warning rounded-3" onclick="return confirm('Are you sure you want to delete this PDF?')">
                    <i class="fas fa-trash me-2"></i> Delete PDF
                </button>
            @else
                <button wire:click="generateAndSavePDF" class="btn btn-danger rounded-3">
                    <i class="fas fa-file-pdf me-2"></i> Generate & Download PDF
                </button>
            @endif
            
            <!-- Status Update -->
            <select wire:change="updateStatus($event.target.value)" class="form-select rounded-3" style="width: 140px;">
                <option value="draft" {{ $estimate->status == 'draft' ? 'selected' : '' }}>📄 Draft</option>
                <option value="sent" {{ $estimate->status == 'sent' ? 'selected' : '' }}>📨 Sent</option>
                <option value="accepted" {{ $estimate->status == 'accepted' ? 'selected' : '' }}>✅ Accepted</option>
                <option value="expired" {{ $estimate->status == 'expired' ? 'selected' : '' }}>⏰ Expired</option>
                <option value="converted" {{ $estimate->status == 'converted' ? 'selected' : '' }}>🔄 Converted</option>
            </select>
            
            <a href="{{ route('admin.estimates.index') }}" class="btn btn-secondary rounded-3">
                <i class="fas fa-arrow-left me-2"></i> Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <!-- Estimate Information Card -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Estimate Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td width="150"><strong>Estimate #:</strong></td><td>{{ $estimate->estimate_number }}</td></tr>
                                <tr><td><strong>Title:</strong></td><td>{{ $estimate->title }}</td></tr>
                                <tr><td><strong>Date Issued:</strong></td><td>{{ $estimate->created_at->format('F d, Y') }}</td></tr>
                                <tr><td><strong>Valid Until:</strong></td><td>{{ $estimate->valid_until ? date('F d, Y', strtotime($estimate->valid_until)) : 'N/A' }}</td></tr>
                                <tr><td><strong>Country:</strong></td><td>{{ $estimate->country->name ?? 'N/A' }} ({{ $estimate->currency_symbol }})</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td width="150"><strong>Client:</strong></td><td><strong>{{ $estimate->client->name }}</strong></td></tr>
                                <tr><td><strong>Email:</strong></td><td>{{ $estimate->client->email }}</td></tr>
                                @if($estimate->client->phone)<tr><td><strong>Phone:</strong></td><td>{{ $estimate->client->phone }}</td></tr>@endif
                                @if($estimate->client->company)<tr><td><strong>Company:</strong></td><td>{{ $estimate->client->company }}</td></tr>@endif
                                <tr><td><strong>Service Type:</strong></td><td>{{ $estimate->serviceType->name ?? 'N/A' }}</td></tr>
                                <tr><td><strong>Package:</strong></td><td>{{ $estimate->pricingPlan->name ?? 'Custom' }}</td></tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($estimate->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="mt-1">{{ $estimate->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Items Card -->
        <div class="col-md-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i> Estimate Items</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item / Service</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estimate->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td>{{ $item->description ?? '—' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>{{ $estimate->currency_symbol ?? '$' }}{{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $estimate->currency_symbol ?? '$' }}{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                                    <td>{{ $estimate->currency_symbol ?? '$' }}{{ number_format($estimate->subtotal, 2) }}</td>
                                </tr>
                                @if($estimate->tax_rate > 0)
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Tax ({{ $estimate->tax_rate }}%):</td>
                                    <td>{{ $estimate->currency_symbol ?? '$' }}{{ number_format($estimate->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="5" class="text-end fw-bold">Total Amount:</td>
                                    <td class="fw-bold text-primary">{{ $estimate->currency_symbol ?? '$' }}{{ number_format($estimate->total, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notes & Terms -->
        @if($estimate->notes || $estimate->terms_conditions)
            <div class="col-md-12 mb-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-gradient-primary text-white rounded-top-4">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i> Notes & Terms</h5>
                    </div>
                    <div class="card-body p-4">
                        @if($estimate->notes)
                            <div class="mb-3">
                                <strong>Notes:</strong>
                                <p class="mt-1">{{ $estimate->notes }}</p>
                            </div>
                        @endif
                        @if($estimate->terms_conditions)
                            <div>
                                <strong>Terms & Conditions:</strong>
                                <p class="mt-1">{{ $estimate->terms_conditions }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
        
        <!-- PDF Status Card -->
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h5 class="mb-0"><i class="fas fa-file-pdf me-2"></i> PDF Document</h5>
                </div>
                <div class="card-body p-4">
                    @if($estimate->pdf_path)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            PDF is saved and available for download.
                            <a href="#" wire:click="downloadSavedPDF" class="alert-link ms-3">Download PDF</a>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            No PDF generated yet. Click "Generate & Download PDF" to create and save one.
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
    .btn-group {
        display: flex;
        gap: 8px;
    }
</style>