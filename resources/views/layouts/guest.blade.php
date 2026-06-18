<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login') }} — {{ \App\Models\Setting::getValue('company_name', 'ERP System') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            padding: 40px 36px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header .brand-logo {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            object-fit: cover;
            margin-bottom: 12px;
        }
        .login-header .brand-icon {
            width: 64px;
            height: 64px;
            border-radius: 14px;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .login-header .brand-name {
            font-size: 22px;
            font-weight: 700;
            color: #1e293b;
            display: block;
        }
        .login-header .brand-tagline {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #6366f1;
        }
        .form-floating > .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }
        .login-btn {
            background: #6366f1;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: background 0.15s;
        }
        .login-btn:hover { background: #4f46e5; }
        .login-btn:active { background: #4338ca !important; }
        .form-check-input:checked { background-color: #6366f1; border-color: #6366f1; }
        .form-check-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12); }
        .forgot-link { color: #6366f1; font-size: 14px; text-decoration: none; }
        .forgot-link:hover { color: #4f46e5; text-decoration: underline; }
        .alert-danger { border-radius: 10px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            @php
                $logo = \App\Models\Setting::getValue('company_logo');
                $name = \App\Models\Setting::getValue('company_name', 'ERP System');
            @endphp
            @if (!empty($logo))
                <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="brand-logo">
            @else
                <div class="brand-icon">{{ strtoupper(substr($name, 0, 1)) }}</div>
            @endif
            <span class="brand-name">{{ $name }}</span>
            <span class="brand-tagline">Sign in to your account</span>
        </div>

        {{ $slot }}
    </div>
</body>
</html>
