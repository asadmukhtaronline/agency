<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - WebInventorz</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }
        
        /* Animated Background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
            pointer-events: none;
        }
        
        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .login-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.3);
        }
        
        .login-header {
            background: linear-gradient(135deg, #FE171B, #cc1215);
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .logo-container {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .logo-circle i {
            font-size: 40px;
            color: #FE171B;
        }
        
        .login-header h3 {
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 2px;
            position: relative;
            z-index: 1;
        }
        
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }
        
        .login-body {
            padding: 40px;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #FE171B;
            box-shadow: 0 0 0 0.2rem rgba(254, 23, 27, 0.25);
        }
        
        .input-group-text {
            border-radius: 12px 0 0 12px;
            background: white;
            border-right: none;
        }
        
        .input-group .form-control {
            border-radius: 0 12px 12px 0;
            border-left: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #FE171B, #cc1215);
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(254, 23, 27, 0.4);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
        }
        
        .form-check-input:checked {
            background-color: #FE171B;
            border-color: #FE171B;
        }
        
        a {
            color: #FE171B;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        a:hover {
            color: #cc1215;
            text-decoration: underline;
        }
        
        /* Demo Credentials Box */
        .demo-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .demo-box:hover {
            border-color: #FE171B;
            box-shadow: 0 5px 15px rgba(254, 23, 27, 0.1);
        }
        
        .demo-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #FE171B;
        }
        
        .demo-credential {
            transition: all 0.2s ease;
        }
        
        .demo-credential:hover {
            background: white;
            border-radius: 8px;
            transform: scale(1.02);
        }
        
        /* Footer */
        .footer-text {
            font-size: 12px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }
        
        .footer-text:hover {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5 col-lg-4">
                <div class="login-card">
                    <div class="login-header">
                        <div class="logo-container">
                            <div class="logo-circle">
                                <img src="{{ asset('logo.png') }}" alt="WebInventorz" style="width: 50px; height: 50px; object-fit: contain;">
                            </div>
                        </div>
                        <p>Admin Login Panel</p>
                    </div>
                    <div class="login-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                @foreach($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('login.post') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope text-primary"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}" placeholder="admin@example.com" required autofocus>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock text-primary"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                           placeholder="••••••••" required>
                                </div>
                            </div>
                            
                            <div class="mb-3 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">Remember Me</label>
                                </div>
                                <a href="#" class="small">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-login w-100 text-white">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i> Secure Admin Access
                            </small>
                        </div>
                        
                        <!-- Demo Credentials -->
                        <div class="demo-box mt-4 p-3">
                            <div class="text-center mb-2">
                                <span class="demo-title">
                                    <i class="fas fa-info-circle me-1"></i> Demo Credentials
                                </span>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="demo-credential p-2">
                                        <small class="text-muted d-block">Super Admin</small>
                                        <small class="fw-semibold">superadmin@webinventorz.com</small>
                                        <small class="text-muted d-block">password123</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="demo-credential p-2">
                                        <small class="text-muted d-block">Admin</small>
                                        <small class="fw-semibold">admin@webinventorz.com</small>
                                        <small class="text-muted d-block">password123</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <small class="text-white footer-text">
                        <i class="fas fa-copyright me-1"></i> {{ date('Y') }} WebInventorz - All Rights Reserved
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>