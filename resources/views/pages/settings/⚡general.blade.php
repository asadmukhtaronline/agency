<?php

use Livewire\Component;
use App\Models\Setting;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;
    
    // General Settings
    public $site_name = '';
    public $site_title = '';
    public $site_description = '';
    public $site_keywords = '';
    public $site_logo = null;
    public $existing_logo = '';
    public $site_favicon = null;
    public $existing_favicon = '';
    
    // Contact Settings
    public $contact_email = '';
    public $contact_phone = '';
    public $contact_phone_2 = '';
    public $contact_address = '';
    public $contact_map_url = '';
    
    // Social Settings
    public $social_facebook = '';
    public $social_twitter = '';
    public $social_instagram = '';
    public $social_linkedin = '';
    public $social_youtube = '';
    public $social_github = '';
    
    // SEO Settings
    public $seo_title = '';
    public $seo_description = '';
    public $seo_keywords = '';
    public $seo_author = '';
    public $google_analytics_id = '';
    public $google_verification = '';
    
    // Business Settings
    public $business_hours = '';
    public $business_saturday = '';
    public $business_sunday = '';
    
    // Footer Settings
    public $footer_copyright = '';
    public $footer_description = '';
    
    // Email Settings
    public $email_contact = '';
    public $email_support = '';
    
    // Other Settings
    public $timezone = '';
    public $date_format = '';
    
    public $activeTab = 'general';
    
    public function mount()
    {
        $this->loadSettings();
    }
    
    public function loadSettings()
    {
        // General
        $this->site_name = Setting::get('site_name', '');
        $this->site_title = Setting::get('site_title', '');
        $this->site_description = Setting::get('site_description', '');
        $this->site_keywords = Setting::get('site_keywords', '');
        $this->existing_logo = Setting::get('site_logo', '');
        $this->existing_favicon = Setting::get('site_favicon', '');
        
        // Contact
        $this->contact_email = Setting::get('contact_email', '');
        $this->contact_phone = Setting::get('contact_phone', '');
        $this->contact_phone_2 = Setting::get('contact_phone_2', '');
        $this->contact_address = Setting::get('contact_address', '');
        $this->contact_map_url = Setting::get('contact_map_url', '');
        
        // Social
        $this->social_facebook = Setting::get('social_facebook', '');
        $this->social_twitter = Setting::get('social_twitter', '');
        $this->social_instagram = Setting::get('social_instagram', '');
        $this->social_linkedin = Setting::get('social_linkedin', '');
        $this->social_youtube = Setting::get('social_youtube', '');
        $this->social_github = Setting::get('social_github', '');
        
        // SEO
        $this->seo_title = Setting::get('seo_title', '');
        $this->seo_description = Setting::get('seo_description', '');
        $this->seo_keywords = Setting::get('seo_keywords', '');
        $this->seo_author = Setting::get('seo_author', '');
        $this->google_analytics_id = Setting::get('google_analytics_id', '');
        $this->google_verification = Setting::get('google_verification', '');
        
        // Business
        $this->business_hours = Setting::get('business_hours', '');
        $this->business_saturday = Setting::get('business_saturday', '');
        $this->business_sunday = Setting::get('business_sunday', '');
        
        // Footer
        $this->footer_copyright = Setting::get('footer_copyright', '');
        $this->footer_description = Setting::get('footer_description', '');
        
        // Email
        $this->email_contact = Setting::get('email_contact', '');
        $this->email_support = Setting::get('email_support', '');
        
        // Other
        $this->timezone = Setting::get('timezone', '');
        $this->date_format = Setting::get('date_format', '');
    }
    
    public function saveGeneral()
    {
        $this->validate([
            'site_name' => 'required|max:255',
            'site_title' => 'required|max:255',
            'site_description' => 'nullable|max:500',
            'site_keywords' => 'nullable|max:500',
            'site_logo' => 'nullable|image|max:2048',
            'site_favicon' => 'nullable|image|max:1024',
        ]);
        
        // Handle logo upload
        if ($this->site_logo) {
            if ($this->existing_logo && \Storage::disk('public')->exists($this->existing_logo)) {
                \Storage::disk('public')->delete($this->existing_logo);
            }
            $this->existing_logo = $this->site_logo->store('settings', 'public');
        }
        
        // Handle favicon upload
        if ($this->site_favicon) {
            if ($this->existing_favicon && \Storage::disk('public')->exists($this->existing_favicon)) {
                \Storage::disk('public')->delete($this->existing_favicon);
            }
            $this->existing_favicon = $this->site_favicon->store('settings', 'public');
        }
        
        Setting::set('site_name', $this->site_name);
        Setting::set('site_title', $this->site_title);
        Setting::set('site_description', $this->site_description);
        Setting::set('site_keywords', $this->site_keywords);
        Setting::set('site_logo', $this->existing_logo);
        Setting::set('site_favicon', $this->existing_favicon);
        
        session()->flash('message', 'General settings saved successfully!');
    }
    
    public function saveContact()
    {
        $this->validate([
            'contact_email' => 'required|email',
            'contact_phone' => 'required|max:50',
        ]);
        
        Setting::set('contact_email', $this->contact_email);
        Setting::set('contact_phone', $this->contact_phone);
        Setting::set('contact_phone_2', $this->contact_phone_2);
        Setting::set('contact_address', $this->contact_address);
        Setting::set('contact_map_url', $this->contact_map_url);
        
        session()->flash('message', 'Contact settings saved successfully!');
    }
    
    public function saveSocial()
    {
        Setting::set('social_facebook', $this->social_facebook);
        Setting::set('social_twitter', $this->social_twitter);
        Setting::set('social_instagram', $this->social_instagram);
        Setting::set('social_linkedin', $this->social_linkedin);
        Setting::set('social_youtube', $this->social_youtube);
        Setting::set('social_github', $this->social_github);
        
        session()->flash('message', 'Social settings saved successfully!');
    }
    
    public function saveSEO()
    {
        Setting::set('seo_title', $this->seo_title);
        Setting::set('seo_description', $this->seo_description);
        Setting::set('seo_keywords', $this->seo_keywords);
        Setting::set('seo_author', $this->seo_author);
        Setting::set('google_analytics_id', $this->google_analytics_id);
        Setting::set('google_verification', $this->google_verification);
        
        session()->flash('message', 'SEO settings saved successfully!');
    }
    
    public function saveBusiness()
    {
        Setting::set('business_hours', $this->business_hours);
        Setting::set('business_saturday', $this->business_saturday);
        Setting::set('business_sunday', $this->business_sunday);
        
        session()->flash('message', 'Business hours saved successfully!');
    }
    
    public function saveFooter()
    {
        Setting::set('footer_copyright', $this->footer_copyright);
        Setting::set('footer_description', $this->footer_description);
        
        session()->flash('message', 'Footer settings saved successfully!');
    }
    
    public function saveEmail()
    {
        Setting::set('email_contact', $this->email_contact);
        Setting::set('email_support', $this->email_support);
        
        session()->flash('message', 'Email settings saved successfully!');
    }
    
    public function saveOther()
    {
        Setting::set('timezone', $this->timezone);
        Setting::set('date_format', $this->date_format);
        
        session()->flash('message', 'Other settings saved successfully!');
    }
    
    public function removeLogo()
    {
        if ($this->existing_logo && \Storage::disk('public')->exists($this->existing_logo)) {
            \Storage::disk('public')->delete($this->existing_logo);
        }
        $this->existing_logo = '';
        Setting::set('site_logo', '');
        $this->site_logo = null;
        session()->flash('message', 'Logo removed successfully!');
    }
    
    public function removeFavicon()
    {
        if ($this->existing_favicon && \Storage::disk('public')->exists($this->existing_favicon)) {
            \Storage::disk('public')->delete($this->existing_favicon);
        }
        $this->existing_favicon = '';
        Setting::set('site_favicon', '');
        $this->site_favicon = null;
        session()->flash('message', 'Favicon removed successfully!');
    }
};

?>

<div>
    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">General Settings</h2>
            <p class="text-muted">Manage your website configuration and settings</p>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'general' ? 'active' : '' }}" wire:click="$set('activeTab', 'general')">
                        <i class="fas fa-globe me-1"></i> General
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'contact' ? 'active' : '' }}" wire:click="$set('activeTab', 'contact')">
                        <i class="fas fa-address-card me-1"></i> Contact
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'social' ? 'active' : '' }}" wire:click="$set('activeTab', 'social')">
                        <i class="fab fa-facebook me-1"></i> Social Media
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'seo' ? 'active' : '' }}" wire:click="$set('activeTab', 'seo')">
                        <i class="fas fa-chart-line me-1"></i> SEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'business' ? 'active' : '' }}" wire:click="$set('activeTab', 'business')">
                        <i class="fas fa-clock me-1"></i> Business Hours
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'footer' ? 'active' : '' }}" wire:click="$set('activeTab', 'footer')">
                        <i class="fas fa-copyright me-1"></i> Footer
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'email' ? 'active' : '' }}" wire:click="$set('activeTab', 'email')">
                        <i class="fas fa-envelope me-1"></i> Email
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'other' ? 'active' : '' }}" wire:click="$set('activeTab', 'other')">
                        <i class="fas fa-cog me-1"></i> Other
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <!-- General Tab -->
            @if($activeTab == 'general')
            <form wire:submit.prevent="saveGeneral">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="site_name">
                        <small class="text-muted">This will appear as your website name</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="site_title">
                        <small class="text-muted">Browser tab title</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Site Description</label>
                        <textarea class="form-control" wire:model="site_description" rows="2"></textarea>
                        <small class="text-muted">Brief description of your website</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Site Keywords</label>
                        <input type="text" class="form-control" wire:model="site_keywords">
                        <small class="text-muted">Comma-separated keywords</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Logo</label>
                        @if($existing_logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $existing_logo) }}" class="img-fluid" style="max-height: 80px;">
                                <div class="mt-1">
                                    <button type="button" class="btn btn-sm btn-danger" wire:click="removeLogo">Remove Logo</button>
                                </div>
                            </div>
                        @endif
                        <input type="file" class="form-control" wire:model="site_logo" accept="image/*">
                        <small class="text-muted">Recommended size: 200x50px (Max: 2MB)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Site Favicon</label>
                        @if($existing_favicon)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $existing_favicon) }}" class="img-fluid" style="max-height: 32px;">
                                <div class="mt-1">
                                    <button type="button" class="btn btn-sm btn-danger" wire:click="removeFavicon">Remove Favicon</button>
                                </div>
                            </div>
                        @endif
                        <input type="file" class="form-control" wire:model="site_favicon" accept="image/*">
                        <small class="text-muted">Recommended size: 32x32px (Max: 1MB)</small>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save General Settings</button>
                </div>
            </form>
            @endif
            
            <!-- Contact Tab -->
            @if($activeTab == 'contact')
            <form wire:submit.prevent="saveContact">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" wire:model="contact_email">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="contact_phone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alternate Phone</label>
                        <input type="text" class="form-control" wire:model="contact_phone_2">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Google Map URL</label>
                        <input type="text" class="form-control" wire:model="contact_map_url">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Address</label>
                        <textarea class="form-control" wire:model="contact_address" rows="2"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Contact Settings</button>
                </div>
            </form>
            @endif
            
            <!-- Social Tab -->
            @if($activeTab == 'social')
            <form wire:submit.prevent="saveSocial">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-facebook text-primary me-1"></i> Facebook</label>
                        <input type="url" class="form-control" wire:model="social_facebook" placeholder="https://facebook.com/yourpage">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-twitter text-info me-1"></i> Twitter</label>
                        <input type="url" class="form-control" wire:model="social_twitter" placeholder="https://twitter.com/yourhandle">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-instagram text-danger me-1"></i> Instagram</label>
                        <input type="url" class="form-control" wire:model="social_instagram" placeholder="https://instagram.com/yourpage">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-linkedin text-primary me-1"></i> LinkedIn</label>
                        <input type="url" class="form-control" wire:model="social_linkedin" placeholder="https://linkedin.com/company/yourcompany">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-youtube text-danger me-1"></i> YouTube</label>
                        <input type="url" class="form-control" wire:model="social_youtube" placeholder="https://youtube.com/c/yourchannel">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fab fa-github text-dark me-1"></i> GitHub</label>
                        <input type="url" class="form-control" wire:model="social_github" placeholder="https://github.com/yourusername">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Social Settings</button>
                </div>
            </form>
            @endif
            
            <!-- SEO Tab -->
            @if($activeTab == 'seo')
            <form wire:submit.prevent="saveSEO">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">SEO Title</label>
                        <input type="text" class="form-control" wire:model="seo_title">
                        <small class="text-muted">Recommended length: 50-60 characters</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">SEO Description</label>
                        <textarea class="form-control" wire:model="seo_description" rows="2"></textarea>
                        <small class="text-muted">Recommended length: 150-160 characters</small>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">SEO Keywords</label>
                        <input type="text" class="form-control" wire:model="seo_keywords">
                        <small class="text-muted">Comma-separated keywords</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SEO Author</label>
                        <input type="text" class="form-control" wire:model="seo_author">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Google Analytics ID</label>
                        <input type="text" class="form-control" wire:model="google_analytics_id" placeholder="G-XXXXXXXXXX">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Google Search Console Verification</label>
                        <input type="text" class="form-control" wire:model="google_verification" placeholder="google-site-verification=xxxxxxxxxx">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save SEO Settings</button>
                </div>
            </form>
            @endif
            
            <!-- Business Hours Tab -->
            @if($activeTab == 'business')
            <form wire:submit.prevent="saveBusiness">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Weekdays (Mon-Fri)</label>
                        <input type="text" class="form-control" wire:model="business_hours">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Saturday</label>
                        <input type="text" class="form-control" wire:model="business_saturday">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Sunday</label>
                        <input type="text" class="form-control" wire:model="business_sunday">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Business Hours</button>
                </div>
            </form>
            @endif
            
            <!-- Footer Tab -->
            @if($activeTab == 'footer')
            <form wire:submit.prevent="saveFooter">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Footer Description</label>
                        <textarea class="form-control" wire:model="footer_description" rows="2"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Copyright Text</label>
                        <input type="text" class="form-control" wire:model="footer_copyright">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Footer Settings</button>
                </div>
            </form>
            @endif
            
            <!-- Email Tab -->
            @if($activeTab == 'email')
            <form wire:submit.prevent="saveEmail">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-control" wire:model="email_contact">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Support Email</label>
                        <input type="email" class="form-control" wire:model="email_support">
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Email Settings</button>
                </div>
            </form>
            @endif
            
            <!-- Other Tab -->
            @if($activeTab == 'other')
            <form wire:submit.prevent="saveOther">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Timezone</label>
                        <select class="form-select" wire:model="timezone">
                            <option value="Asia/Karachi">Asia/Karachi (PKT)</option>
                            <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                            <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                            <option value="Europe/London">Europe/London (GMT)</option>
                            <option value="UTC">UTC</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Date Format</label>
                        <select class="form-select" wire:model="date_format">
                            <option value="M d, Y">Jan 15, 2024</option>
                            <option value="d/m/Y">15/01/2024</option>
                            <option value="m/d/Y">01/15/2024</option>
                            <option value="Y-m-d">2024-01-15</option>
                            <option value="F d, Y">January 15, 2024</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Save Other Settings</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

<style>
    .card-header {
        background: white;
        border-bottom: 1px solid #eef2f6;
        padding: 0;
    }
    .nav-tabs {
        border-bottom: none;
        padding: 0 1rem;
    }
    .nav-tabs .nav-link {
        border: none;
        padding: 1rem 1.5rem;
        color: #64748b;
        font-weight: 500;
        transition: all 0.3s;
    }
    .nav-tabs .nav-link:hover {
        color: #FE171B;
        background: transparent;
    }
    .nav-tabs .nav-link.active {
        color: #FE171B;
        border-bottom: 2px solid #FE171B;
        background: transparent;
    }
    .form-control:focus, .form-select:focus {
        border-color: #FE171B;
        box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
    }
    .btn-primary {
        background: #FE171B;
        border: none;
    }
    .btn-primary:hover {
        background: #cc1215;
    }
</style>