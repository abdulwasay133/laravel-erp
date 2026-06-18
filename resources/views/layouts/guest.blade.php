<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login') }} — {{ \App\Models\Setting::getValue('company_name', 'ERP System') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1E3A4C 0%, #2D5A6E 100%);
            padding: 20px;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(30, 58, 76, 0.25);
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
            background: linear-gradient(135deg, #85D1DB 0%, #5FB8C5 100%);
            color: #1E3A4C;
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
            color: #1E3A4C;
            font-family: 'Poppins', 'Inter', sans-serif;
            display: block;
        }
        .login-header .brand-tagline {
            font-size: 13px;
            color: #9CA3AF;
            margin-top: 4px;
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: #85D1DB;
        }
        .form-floating > .form-control:focus {
            border-color: #85D1DB;
            box-shadow: 0 0 0 3px rgba(133, 209, 219, 0.12);
        }
        .login-btn {
            background: #85D1DB;
            border: none;
            padding: 12px;
            font-weight: 600;
            color: #1E3A4C;
            border-radius: 10px;
            transition: all 0.2s ease;
        }
        .login-btn:hover { background: #6CC4D0; color: #1E3A4C; }
        .login-btn:active { background: #5BB5C2 !important; color: #1E3A4C !important; }
        .form-check-input:checked { background-color: #85D1DB; border-color: #85D1DB; }
        .form-check-input:focus { border-color: #85D1DB; box-shadow: 0 0 0 3px rgba(133, 209, 219, 0.12); }
        .forgot-link { color: #1E3A4C; font-size: 14px; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { color: #85D1DB; text-decoration: underline; }
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
