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
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .login-header {
            padding: 30px;
            text-align: center;
        }
        
        .login-header h2 {
            color: white;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        
        .login-header p {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            margin: 8px 0 0;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #FE171B;
            box-shadow: 0 0 0 3px rgba(254, 23, 27, 0.1);
        }
        
        .input-group {
            display: flex;
            align-items: stretch;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-right: none;
            border-radius: 8px 0 0 8px;
            display: flex;
            align-items: center;
            padding: 0 15px;
            color: #666;
        }
        
        .input-group .form-control {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }
        
        .checkbox-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group label {
            margin: 0;
            font-weight: normal;
        }
        
        .btn-login {
            width: 100%;
            background: #FE171B;
            border: none;
            padding: 12px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #cc1215;
            transform: translateY(-2px);
        }
        
        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            font-size: 12px;
            color: #999;
        }
        
        .demo-box {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .demo-title {
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #FE171B;
            margin-bottom: 10px;
            display: block;
        }
        
        .demo-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }
        
        .demo-col {
            flex: 1;
            background: white;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            font-size: 11px;
        }
        
        .demo-col strong {
            display: block;
            color: #333;
        }
        
        .text-muted {
            color: #666;
            font-size: 12px;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn-close {
            float: right;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
        }
        
        a {
            color: #FE171B;
            text-decoration: none;
            font-size: 13px;
        }
        
        a:hover {
            text-decoration: underline;
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.7);
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="{{ asset('logo.png') }}" width="200px" alt="">
            </div>
            <div class="login-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <button class="btn-close" data-bs-dismiss="alert">×</button>
                        {{ session('success') }}
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger">
                        <button class="btn-close" data-bs-dismiss="alert">×</button>
                        @foreach($errors->all() as $error)
                            {{ $error }}
                        @endforeach
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control" 
                                   value="{{ old('email') }}" placeholder="admin@example.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" 
                                   placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="remember" value="1"> Remember Me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>
            </div>
        </div>
        <div class="footer">
            © {{ date('Y') }} WebInventorz - All Rights Reserved
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.btn-close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>