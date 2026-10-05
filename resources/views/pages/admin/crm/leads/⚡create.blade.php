<?php

use Livewire\Component;
use App\Models\ClientType;
use App\Models\WorkType;
use App\Models\Country;
use App\Models\ClientLead;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    // Basic Information
    public $company_name = '';
    public $contact_person = '';
    public $email = '';
    public $phone = '';
    public $alternative_phone = '';
    public $has_whatsapp = false;
    public $whatsapp_number = '';
    public $website = '';
    
    // Social Media Links
    public $facebook = '';
    public $instagram = '';
    public $twitter = '';
    public $linkedin = '';
    public $pinterest = '';
    public $reddit = '';
    public $youtube = '';
    public $tiktok = '';
    
    // Country
    public $country_id = '';
    
    // Screenshots
    public $screenshots = [];
    public $existingScreenshots = [];
    
    // Other Information
    public $client_type_id = '';
    public $work_type_id = '';
    public $requirements = '';
    public $notes = '';
    public $status = 'new';
    public $priority = 'medium';
    public $next_followup = null;
    public $source = '';
    public $address = '';
    public $city = '';
    
    public function updatedHasWhatsapp($value)
    {
        if ($value) {
            $this->whatsapp_number = $this->phone;
        } else {
            $this->whatsapp_number = '';
        }
    }
    
    public function updatedPhone($value)
    {
        if ($this->has_whatsapp) {
            $this->whatsapp_number = $value;
        }
    }
    
    public function removeScreenshot($index)
    {
        unset($this->existingScreenshots[$index]);
        $this->existingScreenshots = array_values($this->existingScreenshots);
    }
    
    public function save()
    {
        $this->validate([
            'company_name' => 'required|min:2|max:255',
            'contact_person' => 'nullable|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|max:50',
            'alternative_phone' => 'nullable|max:50',
            'has_whatsapp' => 'boolean',
            'whatsapp_number' => 'nullable|max:50',
            'website' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'linkedin' => 'nullable|url|max:255',
            'pinterest' => 'nullable|url|max:255',
            'reddit' => 'nullable|url|max:255',
            'youtube' => 'nullable|url|max:255',
            'tiktok' => 'nullable|url|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'client_type_id' => 'nullable|exists:client_types,id',
            'work_type_id' => 'nullable|exists:work_types,id',
            'requirements' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:new,contacted,meeting,proposal,negotiation,won,lost',
            'priority' => 'required|in:low,medium,high',
            'next_followup' => 'nullable|date',
            'source' => 'nullable|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|max:100',
        ]);
        
        // Handle screenshots upload
        $screenshotPaths = $this->existingScreenshots;
        if ($this->screenshots) {
            foreach ($this->screenshots as $screenshot) {
                $screenshotPaths[] = $screenshot->store('client-screenshots', 'public');
            }
        }
        
        // Get country details
        $countryCode = null;
        if ($this->country_id) {
            $country = Country::find($this->country_id);
            $countryCode = $country->code ?? null;
        }
        
        // Create lead
        ClientLead::create([
            'company_name' => $this->company_name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'alternative_phone' => $this->alternative_phone,
            'has_whatsapp' => $this->has_whatsapp,
            'whatsapp_number' => $this->whatsapp_number,
            'website' => $this->website,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'pinterest' => $this->pinterest,
            'reddit' => $this->reddit,
            'youtube' => $this->youtube,
            'tiktok' => $this->tiktok,
            'country_id' => $this->country_id,
            'country_code' => $countryCode,
            'screenshots' => json_encode($screenshotPaths),
            'client_type_id' => $this->client_type_id,
            'work_type_id' => $this->work_type_id,
            'requirements' => $this->requirements,
            'notes' => $this->notes,
            'status' => $this->status,
            'priority' => $this->priority,
           // 'next_followup' => $this->next_followup,
            'source' => $this->source,
            'address' => $this->address,
            'city' => $this->city,
            'is_active' => true,
        ]);
        
        session()->flash('message', 'Lead created successfully!');
        return redirect()->route('admin.crm.leads.index');
    }
    
    public function getClientTypesProperty()
    {
        return ClientType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getWorkTypesProperty()
    {
        return WorkType::where('is_active', true)->orderBy('name')->get();
    }
    
    public function getCountriesProperty()
    {
        return Country::where('is_active', true)->orderBy('name')->get();
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
            <h2 class="fw-bold mb-0">Add New Lead</h2>
            <p class="text-muted">Create a new client lead for hunting</p>
        </div>
        <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary rounded-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Leads
        </a>
    </div>
    
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-gradient-primary text-white rounded-top-4">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Lead Information</h5>
        </div>
        <div class="card-body p-4">
            <form wire:submit.prevent="save">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-building text-primary me-2"></i> Basic Information
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3 @error('company_name') is-invalid @enderror" 
                                   wire:model="company_name" placeholder="e.g., ABC Technologies">
                            @error('company_name') 
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Contact Person</label>
                            <input type="text" class="form-control rounded-3 @error('contact_person') is-invalid @enderror" 
                                   wire:model="contact_person" placeholder="e.g., John Doe">
                            @error('contact_person') 
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control rounded-3 @error('email') is-invalid @enderror" 
                                   wire:model="email" placeholder="contact@company.com">
                            @error('email') 
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Phone Numbers Section -->
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <label class="form-label fw-semibold mb-2">
                                    <i class="fas fa-phone text-primary me-1"></i> Contact Numbers
                                </label>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Primary Number</label>
                                    <input type="text" class="form-control rounded-3 @error('phone') is-invalid @enderror" 
                                           wire:model="phone" placeholder="+92 300 1234567">
                                    @error('phone') 
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Alternative Number</label>
                                    <input type="text" class="form-control rounded-3 @error('alternative_phone') is-invalid @enderror" 
                                           wire:model="alternative_phone" placeholder="+92 300 7654321">
                                    @error('alternative_phone') 
                                        <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" class="form-check-input" id="has_whatsapp" wire:model="has_whatsapp">
                                    <label class="form-check-label" for="has_whatsapp">
                                        <i class="fab fa-whatsapp text-success me-1"></i> Client has WhatsApp
                                    </label>
                                </div>
                                
                                @if($has_whatsapp)
                                    <div>
                                        <label class="form-label small">WhatsApp Number</label>
                                        <input type="text" class="form-control rounded-3 @error('whatsapp_number') is-invalid @enderror" 
                                               wire:model="whatsapp_number" placeholder="+92 300 1234567">
                                        @error('whatsapp_number') 
                                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Same as primary number by default</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Website</label>
                            <input type="url" class="form-control rounded-3 @error('website') is-invalid @enderror" 
                                   wire:model="website" placeholder="https://www.company.com">
                            @error('website') 
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>
                        
                        <h6 class="fw-bold mb-3 mt-3 pb-2 border-bottom">
                            <i class="fas fa-map-marker-alt text-primary me-2"></i> Location
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Country</label>
                                <select class="form-select rounded-3 @error('country_id') is-invalid @enderror" wire:model="country_id">
                                    <option value="">Select Country</option>
                                    @foreach($this->countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }} ({{ $country->code }})</option>
                                    @endforeach
                                </select>
                                @error('country_id') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">City</label>
                                <input type="text" class="form-control rounded-3 @error('city') is-invalid @enderror" 
                                       wire:model="city" placeholder="e.g., Lahore">
                                @error('city') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Address</label>
                            <input type="text" class="form-control rounded-3 @error('address') is-invalid @enderror" 
                                   wire:model="address" placeholder="Street address">
                            @error('address') 
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3 pb-2 border-bottom">
                            <i class="fas fa-chart-line text-primary me-2"></i> Lead Details
                        </h6>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Client Type</label>
                                <select class="form-select rounded-3 @error('client_type_id') is-invalid @enderror" wire:model="client_type_id">
                                    <option value="">Select Client Type</option>
                                    @foreach($this->clientTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('client_type_id') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Work Type</label>
                                <select class="form-select rounded-3 @error('work_type_id') is-invalid @enderror" wire:model="work_type_id">
                                    <option value="">Select Work Type</option>
                                    @foreach($this->workTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('work_type_id') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select rounded-3 @error('status') is-invalid @enderror" wire:model="status">
                                    <option value="new">🆕 New</option>
                                    <option value="contacted">📞 Contacted</option>
                                    <option value="meeting">🤝 Meeting</option>
                                    <option value="proposal">📄 Proposal</option>
                                    <option value="negotiation">💬 Negotiation</option>
                                    <option value="won">✅ Won</option>
                                    <option value="lost">❌ Lost</option>
                                </select>
                                @error('status') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Priority</label>
                                <select class="form-select rounded-3 @error('priority') is-invalid @enderror" wire:model="priority">
                                    <option value="low">🟢 Low</option>
                                    <option value="medium">🟡 Medium</option>
                                    <option value="high">🔴 High</option>
                                </select>
                                @error('priority') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Source</label>
                                <select class="form-select rounded-3 @error('source') is-invalid @enderror" wire:model="source">
                                    <option value="">Select Source</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="referral">Referral</option>
                                    <option value="google">Google Search</option>
                                    <option value="email">Email</option>
                                    <option value="call">Call</option>
                                    <option value="event">Event</option>
                                </select>
                                @error('source') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Next Follow-up</label>
                                <input type="date" class="form-control rounded-3 @error('next_followup') is-invalid @enderror" 
                                       wire:model="next_followup">
                                @error('next_followup') 
                                    <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3 mt-3 pb-2 border-bottom">
                            <i class="fab fa-facebook text-primary me-2"></i> Social Media Links
                        </h6>
                        
                        <div class="mb-2">
                            <label class="form-label small"><i class="fab fa-facebook text-primary me-1"></i> Facebook</label>
                            <input type="url" class="form-control rounded-3 @error('facebook') is-invalid @enderror" 
                                   wire:model="facebook" placeholder="https://facebook.com/company">
                            @error('facebook') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label small"><i class="fab fa-instagram text-danger me-1"></i> Instagram</label>
                            <input type="url" class="form-control rounded-3 @error('instagram') is-invalid @enderror" 
                                   wire:model="instagram" placeholder="https://instagram.com/company">
                            @error('instagram') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label small"><i class="fab fa-twitter text-info me-1"></i> Twitter</label>
                            <input type="url" class="form-control rounded-3 @error('twitter') is-invalid @enderror" 
                                   wire:model="twitter" placeholder="https://twitter.com/company">
                            @error('twitter') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label small"><i class="fab fa-linkedin text-primary me-1"></i> LinkedIn</label>
                            <input type="url" class="form-control rounded-3 @error('linkedin') is-invalid @enderror" 
                                   wire:model="linkedin" placeholder="https://linkedin.com/company/company">
                            @error('linkedin') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small"><i class="fab fa-pinterest text-danger me-1"></i> Pinterest</label>
                                <input type="url" class="form-control rounded-3 @error('pinterest') is-invalid @enderror" 
                                       wire:model="pinterest" placeholder="https://pinterest.com/company">
                                @error('pinterest') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small"><i class="fab fa-reddit me-1"></i> Reddit</label>
                                <input type="url" class="form-control rounded-3 @error('reddit') is-invalid @enderror" 
                                       wire:model="reddit" placeholder="https://reddit.com/user/company">
                                @error('reddit') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small"><i class="fab fa-youtube text-danger me-1"></i> YouTube</label>
                                <input type="url" class="form-control rounded-3 @error('youtube') is-invalid @enderror" 
                                       wire:model="youtube" placeholder="https://youtube.com/@company">
                                @error('youtube') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small"><i class="fab fa-tiktok me-1"></i> TikTok</label>
                                <input type="url" class="form-control rounded-3 @error('tiktok') is-invalid @enderror" 
                                       wire:model="tiktok" placeholder="https://tiktok.com/@company">
                                @error('tiktok') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <h6 class="fw-bold mb-3 mt-3 pb-2 border-bottom">
                            <i class="fas fa-image text-primary me-2"></i> Screenshots
                        </h6>
                        
                        <div class="mb-3">
                            @if($existingScreenshots)
                                <div class="row g-2 mb-2">
                                    @foreach($existingScreenshots as $index => $img)
                                        <div class="col-3 position-relative">
                                            <img src="{{ asset('storage/' . $img) }}" class="img-fluid rounded-3" style="height: 80px; object-fit: cover; width: 100%;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 rounded-circle" 
                                                    style="width: 20px; height: 20px; padding: 0; font-size: 10px;" 
                                                    wire:click="removeScreenshot({{ $index }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <input type="file" class="form-control rounded-3 @error('screenshots.*') is-invalid @enderror" 
                                   wire:model="screenshots" accept="image/*" multiple>
                            @error('screenshots.*') 
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                            @enderror
                            @if($screenshots)
                                <div class="row g-2 mt-2">
                                    @foreach($screenshots as $index => $img)
                                        <div class="col-3">
                                            <img src="{{ $img->temporaryUrl() }}" class="img-fluid rounded-3" style="height: 80px; object-fit: cover; width: 100%;">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            <small class="text-muted">Upload screenshots (Max 5MB each)</small>
                        </div>
                    </div>
                </div>
                
                <!-- Full Width Fields -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Requirements</label>
                        <textarea class="form-control rounded-3 @error('requirements') is-invalid @enderror" 
                                  wire:model="requirements" rows="3" placeholder="What does the client need?"></textarea>
                        @error('requirements') 
                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea class="form-control rounded-3 @error('notes') is-invalid @enderror" 
                                  wire:model="notes" rows="3" placeholder="Additional notes..."></textarea>
                        @error('notes') 
                            <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle me-1"></i> {{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2 pt-3 border-top mt-3">
                    <a href="{{ route('admin.crm.leads.index') }}" class="btn btn-secondary px-4 py-2 rounded-3">
                        <i class="fas fa-times me-2"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2 rounded-3">
                        <i class="fas fa-save me-2"></i> Save Lead
                    </button>
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
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #cc1215, #FE171B);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(254, 23, 27, 0.3);
    }
    .rounded-4 {
        border-radius: 20px;
    }
    .rounded-3 {
        border-radius: 12px;
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
    }
    .border-bottom {
        border-bottom: 1px solid #dee2e6 !important;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .text-danger {
        font-size: 12px;
    }
</style>