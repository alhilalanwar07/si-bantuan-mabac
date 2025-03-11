<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Bantuan Desa</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .login-bg {
            background: linear-gradient(135deg, #87CEFA, #8FBC8F);
            min-height: 100vh;
        }
        
        .login-box {
            max-width: 450px;
            margin: auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .logo-container {
            margin-bottom: 5px;
            text-align: center;
        }
        
        .logo-container img {
            width: 150px;
            height: auto;
            border-radius: 10px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        
        .input-group-text {
            background: transparent;
            border: none;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #4CAF50, #2196F3);
            border: none;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #45a049, #1e88e5);
        }
        
        .divider {
            border-bottom: 1px solid #ddd;
            margin: 25px 0;
        }
    </style>
</head>
<body class="login-bg">
    <div class="container">
        <div class="row justify-content-center align-items-center vh-100">
            <div class="col-md-6">
                <div class="login-box">
                    <div class="logo-container">
                        <img src="{{ url('/') }}/assets/img/favicon/web-app-manifest-192x192.png"
                             alt="Logo" class="img-fluid">
                    </div>
                    
                    <h3 class="text-center text-primary mb-4">HALAMAN LOGIN</h3>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope text-primary"></i>
                                </span>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required 
                                       autocomplete="current-password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Ingat Saya</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-login text-white">Masuk</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>