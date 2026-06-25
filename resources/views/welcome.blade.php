<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AW Soft Solutions</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #E6F7F9 50%, #D6F1F5 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff; border-radius: 20px; border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 20px 60px rgba(0,0,0,0.06); padding: 48px 40px;
            max-width: 480px; width: 100%; text-align: center;
        }
        .logo {
            width: 56px; height: 56px; border-radius: 14px;
            background: linear-gradient(135deg, #85D1DB, #5FB8C5);
            color: #1E3A4C; display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 800; margin: 0 auto 16px;
        }
        h1 { font-size: 24px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .tagline { font-size: 14px; color: #64748b; margin-bottom: 28px; }
        .info { text-align: left; margin-bottom: 28px; }
        .info-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; font-size: 14px; color: #475569; }
        .info-item i { width: 20px; color: #85D1DB; font-size: 16px; }
        .info-item a { color: #475569; text-decoration: none; }
        .info-item a:hover { color: #1E3A4C; }
        .btn-login {
            display: block; width: 100%; padding: 14px; border-radius: 12px;
            background: #85D1DB; border: none; color: #1E3A4C;
            font-size: 15px; font-weight: 600; text-decoration: none;
            transition: background 0.2s;
        }
        .btn-login:hover { background: #6CC4D0; color: #1E3A4C; }
        .footer { margin-top: 20px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">AW</div>
        <h1>AW Soft Solutions</h1>
        <p class="tagline">Innovative Software Solutions</p>

        <div class="info">
            <div class="info-item">
                <i class="bi bi-telephone-fill"></i>
                <a href="tel:+923439187576">+92 343 9187576</a>
            </div>
            <div class="info-item">
                <i class="bi bi-envelope-fill"></i>
                <a href="mailto:wasayitdik@gmail.com">wasayitdik@gmail.com</a>
            </div>
            <div class="info-item">
                <i class="bi bi-geo-alt-fill"></i>
                Pakistan
            </div>
        </div>

        @if (Route::has('login'))
            @auth
                <a href="{{ route('dashboard') }}" class="btn-login"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="btn-login"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a>
            @endauth
        @endif

        <div class="footer">&copy; {{ date('Y') }} AW Soft Solutions. All rights reserved.</div>
    </div>
</body>
</html>
