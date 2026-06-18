<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Models\Setting::getValue('company_name', 'ERP System') }} — Solutions</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #fff; }

        /* ── Navbar ── */
        .landing-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            background: rgba(255,255,255,0.85); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 12px 0;
        }
        .landing-nav .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .landing-nav .brand-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: linear-gradient(135deg, #85D1DB, #5FB8C5);
            color: #1E3A4C; display: flex; align-items: center; justify-content: center;
            font-size: 16px; font-weight: 700;
        }
        .landing-nav .brand img { width: 36px; height: 36px; border-radius: 10px; object-fit: cover; }
        .landing-nav .brand-name { font-size: 18px; font-weight: 700; color: #1e293b; }
        .landing-nav .btn-login { border-radius: 8px; padding: 8px 20px; font-weight: 500; font-size: 14px; }

        /* ── Hero ── */
        .hero {
            padding: 140px 0 80px;
            background: linear-gradient(135deg, #f8fafc 0%, #E6F7F9 50%, #D6F1F5 100%);
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; top: -50%; right: -20%; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(133,209,219,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero::after {
            content: ''; position: absolute; bottom: -30%; left: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(95,184,197,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(133,209,219,0.12); color: #1E3A4C;
            padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 500;
            margin-bottom: 20px;
        }
        .hero h1 { font-size: clamp(2rem, 5vw, 3.2rem); font-weight: 800; color: #0f172a; line-height: 1.15; }
        .hero h1 .highlight { background: linear-gradient(135deg, #85D1DB, #5FB8C5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero p { font-size: 18px; color: #64748b; max-width: 540px; line-height: 1.7; }
        .hero .btn-primary { border-radius: 10px; padding: 14px 32px; font-weight: 600; font-size: 15px; background: #85D1DB; border: none; color: #1E3A4C; }
        .hero .btn-primary:hover { background: #6CC4D0; color: #1E3A4C; }
        .hero .btn-outline { border-radius: 10px; padding: 14px 32px; font-weight: 600; font-size: 15px; border: 1.5px solid #cbd5e1; color: #475569; }
        .hero .btn-outline:hover { border-color: #85D1DB; color: #1E3A4C; background: rgba(133,209,219,0.06); }
        .hero-dashboard {
            margin-top: 48px; border-radius: 16px; box-shadow: 0 20px 80px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.06); overflow: hidden;
        }

        /* ── Nav links ── */
        .nav-link-custom {
            color: #475569; font-weight: 500; font-size: 14px; padding: 6px 14px !important;
            border-radius: 8px; transition: all 0.2s;
        }
        .nav-link-custom:hover { color: #1E3A4C; background: rgba(133,209,219,0.08); }

        /* ── Features ── */
        .features { padding: 80px 0; }
        .section-title { text-align: center; margin-bottom: 56px; }
        .section-title h2 { font-size: 30px; font-weight: 700; color: #0f172a; }
        .section-title p { color: #64748b; font-size: 16px; max-width: 500px; margin: 8px auto 0; }
        .feature-card {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 28px; transition: all 0.2s; height: 100%;
        }
        .feature-card:hover { border-color: #B3E4EA; background: #E6F7F9; transform: translateY(-2px); }
        .feature-icon {
            width: 48px; height: 48px; border-radius: 12px;
            background: linear-gradient(135deg, #85D1DB, #5FB8C5); color: #1E3A4C;
            display: flex; align-items: center; justify-content: center; font-size: 20px;
            margin-bottom: 16px;
        }
        .feature-card h5 { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 8px; }
        .feature-card p { font-size: 14px; color: #64748b; margin: 0; line-height: 1.6; }

        /* ── Services ── */
        .services { padding: 80px 0; background: #f8fafc; }
        .service-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 32px 24px; text-align: center; transition: all 0.2s; height: 100%;
        }
        .service-card:hover { border-color: #B3E4EA; transform: translateY(-4px); box-shadow: 0 12px 40px rgba(133,209,219,0.1); }
        .service-icon {
            width: 56px; height: 56px; border-radius: 14px; margin: 0 auto 18px;
            background: linear-gradient(135deg, #85D1DB, #5FB8C5); color: #1E3A4C;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
        }
        .service-card h5 { font-size: 17px; font-weight: 600; color: #0f172a; margin-bottom: 8px; }
        .service-card p { font-size: 14px; color: #64748b; margin: 0; line-height: 1.6; }

        /* ── About ── */
        .about { padding: 80px 0; }
        .about h2 { font-size: 30px; font-weight: 700; color: #0f172a; margin-bottom: 16px; }
        .about p { font-size: 16px; color: #475569; line-height: 1.8; max-width: 540px; }
        .about-image {
            border-radius: 16px; background: linear-gradient(135deg, #E6F7F9, #D6F1F5);
            padding: 40px; text-align: center; height: 100%;
            display: flex; align-items: center; justify-content: center;
        }
        .about-image i { font-size: 80px; color: #85D1DB; opacity: 0.7; }
        .stat-card { text-align: center; padding: 24px; }
        .stat-number { font-size: 32px; font-weight: 800; color: #85D1DB; }
        .stat-label { font-size: 14px; color: #64748b; margin-top: 4px; }

        /* ── Pricing ── */
        .pricing { padding: 80px 0; background: #f8fafc; }
        .pricing-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 36px 28px; text-align: center; transition: all 0.2s; height: 100%;
            position: relative;
        }
        .pricing-card:hover { border-color: #B3E4EA; transform: translateY(-4px); box-shadow: 0 12px 40px rgba(133,209,219,0.1); }
        .pricing-card.featured {
            border-color: #85D1DB; background: linear-gradient(135deg, #fff, #E6F7F9);
            transform: scale(1.03);
        }
        .pricing-card.featured:hover { transform: scale(1.03) translateY(-4px); }
        .pricing-badge {
            position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
            background: linear-gradient(135deg, #85D1DB, #5FB8C5); color: #1E3A4C;
            padding: 4px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .pricing-card .plan-name { font-size: 18px; font-weight: 600; color: #0f172a; margin-bottom: 4px; }
        .pricing-card .plan-desc { font-size: 14px; color: #64748b; margin-bottom: 20px; }
        .pricing-card .price { font-size: 42px; font-weight: 800; color: #0f172a; }
        .pricing-card .price span { font-size: 16px; font-weight: 400; color: #94a3b8; }
        .pricing-card .features-list { list-style: none; padding: 0; margin: 20px 0 24px; text-align: left; }
        .pricing-card .features-list li {
            padding: 8px 0; font-size: 14px; color: #475569;
            display: flex; align-items: center; gap: 8px;
        }
        .pricing-card .features-list li i { color: #10b981; font-size: 16px; }
        .pricing-card .btn-pricing {
            border-radius: 10px; padding: 12px; font-weight: 600; font-size: 15px;
            width: 100%; background: #85D1DB; border: none; color: #1E3A4C;
        }
        .pricing-card .btn-pricing:hover { background: #6CC4D0; color: #1E3A4C; }
        .pricing-card.featured .btn-pricing { background: #85D1DB; color: #1E3A4C; }
        .pricing-card.featured .btn-pricing:hover { background: #6CC4D0; color: #1E3A4C; }

        /* ── Footer ── */
        .landing-footer {
            background: #0f172a; color: #94a3b8; padding: 32px 0; font-size: 14px;
        }
        .landing-footer .brand-name { font-weight: 600; color: #e2e8f0; }

        @media (max-width: 768px) {
            .hero { padding: 110px 0 48px; text-align: center; }
            .hero p { margin-left: auto; margin-right: auto; }
            .hero .d-flex { justify-content: center; }
            .landing-nav { padding: 10px 0; }
        }
    </style>
</head>
<body>

{{-- ══ Navbar ═══════════════════════════════════════════ --}}
<nav class="landing-nav">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="/" class="brand">
            @php
                $logo = \App\Models\Setting::getValue('company_logo');
                $name = \App\Models\Setting::getValue('company_name', 'ERP System');
            @endphp
            @if (!empty($logo))
                <img src="{{ asset('storage/' . $logo) }}" alt="Logo">
            @else
                <div class="brand-icon">{{ strtoupper(substr($name, 0, 1)) }}</div>
            @endif
            <span class="brand-name">{{ $name }}</span>
        </a>
        <div class="d-none d-md-flex align-items-center gap-1">
            <a href="#services" class="nav-link-custom">Services</a>
            <a href="#about" class="nav-link-custom">About</a>
            <a href="#pricing" class="nav-link-custom">Pricing</a>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if (Route::has('login'))
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-login"><i class="bi bi-speedometer2 me-1"></i> Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-login">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary btn-login"><i class="bi bi-person-plus me-1"></i> Register</a>
                    @endif
                @endauth
            @endif
        </div>
    </div>
</nav>

{{-- ══ Hero ════════════════════════════════════════════════ --}}
<section class="hero">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-badge"><i class="bi bi-lightning-charge-fill"></i> All-in-One Business Suite</div>
                <h1>Streamline Your Business with <span class="highlight">{{ $name }}</span></h1>
                <p>From sales and inventory to accounting and reporting — manage every aspect of your business from a single, powerful platform.</p>
                <div class="d-flex gap-3 flex-wrap mt-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-speedometer2 me-2"></i> Go to Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-2"></i> Get Started</a>
                        <a href="#features" class="btn btn-outline">Explore Features</a>
                    @endauth
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block">
                <div class="hero-dashboard">
                    <svg viewBox="0 0 600 380" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:auto;display:block;">
                        <rect width="600" height="380" rx="12" fill="#fff"/>
                        <rect x="20" y="16" width="80" height="20" rx="6" fill="#85D1DB"/>
                        <rect x="110" y="16" width="120" height="20" rx="6" fill="#e2e8f0"/>
                        <rect x="560" y="16" width="20" height="20" rx="6" fill="#e2e8f0"/>
                        <rect x="530" y="16" width="20" height="20" rx="6" fill="#e2e8f0"/>
                        <rect x="20" y="56" width="270" height="140" rx="10" fill="#f1f5f9"/>
                        <rect x="30" y="70" width="120" height="10" rx="4" fill="#85D1DB" opacity="0.5"/>
                        <rect x="30" y="88" width="90" height="10" rx="4" fill="#94a3b8"/>
                        <rect x="30" y="104" width="200" height="24" rx="6" fill="#fff" stroke="#e2e8f0" stroke-width="1"/>
                        <rect x="30" y="136" width="200" height="24" rx="6" fill="#fff" stroke="#e2e8f0" stroke-width="1"/>
                        <rect x="30" y="168" width="200" height="24" rx="6" fill="#fff" stroke="#e2e8f0" stroke-width="1"/>
                        <rect x="310" y="56" width="270" height="140" rx="10" fill="#f1f5f9"/>
                        <rect x="320" y="70" width="140" height="10" rx="4" fill="#85D1DB" opacity="0.5"/>
                        <rect x="320" y="104" width="80" height="32" rx="6" fill="#85D1DB"/>
                        <rect x="410" y="104" width="80" height="32" rx="6" fill="#10b981"/>
                        <rect x="500" y="104" width="60" height="32" rx="6" fill="#f59e0b"/>
                        <rect x="320" y="148" width="240" height="8" rx="4" fill="#e2e8f0"/>
                        <rect x="320" y="162" width="180" height="8" rx="4" fill="#e2e8f0"/>
                        <rect x="320" y="176" width="200" height="8" rx="4" fill="#e2e8f0"/>
                        <rect x="20" y="216" width="560" height="140" rx="10" fill="#f8fafc"/>
                        <rect x="30" y="228" width="100" height="10" rx="4" fill="#85D1DB" opacity="0.5"/>
                        <rect x="30" y="250" width="540" height="10" rx="4" fill="#e2e8f0"/>
                        <rect x="30" y="268" width="540" height="10" rx="4" fill="#e2e8f0"/>
                        <rect x="30" y="286" width="540" height="10" rx="4" fill="#e2e8f0"/>
                        <rect x="30" y="304" width="540" height="10" rx="4" fill="#e2e8f0"/>
                        <rect x="30" y="322" width="540" height="10" rx="4" fill="#e2e8f0"/>
                        <rect x="30" y="340" width="200" height="10" rx="4" fill="#e2e8f0"/>
                        <rect x="480" y="338" width="90" height="14" rx="6" fill="#85D1DB"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ Features ════════════════════════════════════════════ --}}
<section class="features" id="features">
    <div class="container">
        <div class="section-title">
            <h2>Everything You Need to Run Your Business</h2>
            <p>Comprehensive modules designed to work together seamlessly.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-cart2"></i></div>
                    <h5>Point of Sale</h5>
                    <p>Fast, intuitive POS with barcode scanning, session management, hold/resume carts, and real-time inventory updates. Accept cash or bank payments.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-cart-check"></i></div>
                    <h5>Sales &amp; Customers</h5>
                    <p>Manage sales orders, invoices, returns, and customer accounts. Track credit customers, view ledgers, and generate sale reports with ease.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-truck"></i></div>
                    <h5>Purchases &amp; Suppliers</h5>
                    <p>Streamline procurement with purchase orders, supplier management, return handling, and supplier ledgers to track payables.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-box"></i></div>
                    <h5>Inventory &amp; Stock</h5>
                    <p>Full inventory control with batch/lot tracking, expiry date monitoring, low-stock alerts, and stock adjustments across multiple warehouses.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-wallet2"></i></div>
                    <h5>Accounting &amp; Finance</h5>
                    <p>Chart of accounts, bank accounts, expense tracking, cash adjustments, and customer/supplier payment management. Opening balance support included.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-bar-chart-line"></i></div>
                    <h5>Reports &amp; Analytics</h5>
                    <p>Comprehensive reports: Profit &amp; Loss, Balance Sheet, Cash Flow, Sale/Purchase reports, ledgers (cash/bank/general/inventory), and daily closing.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-person-badge"></i></div>
                    <h5>HR &amp; Payroll</h5>
                    <p>Employee management with salary processing. Track attendance, manage payroll cycles, and generate salary reports for your workforce.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-file-spreadsheet"></i></div>
                    <h5>Financial Statements</h5>
                    <p>Generate professional Profit &amp; Loss statements, Balance Sheets, and Cash Flow reports with period filtering and print/export support.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon"><i class="bi bi-gear"></i></div>
                    <h5>System &amp; Settings</h5>
                    <p>Customize company profile, configure system preferences, manage users, and control every aspect of your ERP environment.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ Services ═══════════════════════════════════════════ --}}
<section class="services" id="services">
    <div class="container">
        <div class="section-title">
            <h2>Our Services</h2>
            <p>End-to-end solutions tailored to streamline your business operations.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-laptop"></i></div>
                    <h5>ERP Implementation</h5>
                    <p>Full-scale deployment and configuration of your ERP system tailored to your business processes.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-people"></i></div>
                    <h5>Training &amp; Support</h5>
                    <p>Hands-on training for your team and ongoing technical support to ensure smooth daily operations.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                    <h5>Cloud Migration</h5>
                    <p>Seamless migration of your existing data and infrastructure to the cloud for better accessibility.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card">
                    <div class="service-icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Data Security</h5>
                    <p>Enterprise-grade security with role-based access, encrypted backups, and compliance monitoring.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ About Us ═══════════════════════════════════════════ --}}
<section class="about" id="about">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="about-image">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-badge"><i class="bi bi-info-circle"></i> About Us</div>
                <h2>Empowering Businesses with Smart ERP Solutions</h2>
                <p>We are a team of passionate developers and business consultants dedicated to simplifying business management through technology. Our ERP platform is built from the ground up to address the real-world challenges faced by small and medium enterprises.</p>
                <p class="mb-4">From inventory and sales to accounting and HR, we provide a unified ecosystem that eliminates data silos and gives you full visibility into your operations. Thousands of businesses trust us to run their day-to-day operations efficiently.</p>
                <div class="row g-3">
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Businesses Served</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Uptime</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-card">
                            <div class="stat-number">24/7</div>
                            <div class="stat-label">Support</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ Pricing ═══════════════════════════════════════════ --}}
<section class="pricing" id="pricing">
    <div class="container">
        <div class="section-title">
            <h2>Simple, Transparent Pricing</h2>
            <p>Choose the plan that fits your business size and needs. No hidden fees.</p>
        </div>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <div class="plan-name">Starter</div>
                    <div class="plan-desc">For small businesses just getting started</div>
                    <div class="price">$29 <span>/month</span></div>
                    <ul class="features-list">
                        <li><i class="bi bi-check-lg"></i> Up to 10 users</li>
                        <li><i class="bi bi-check-lg"></i> Basic POS &amp; Sales</li>
                        <li><i class="bi bi-check-lg"></i> Inventory management</li>
                        <li><i class="bi bi-check-lg"></i> Purchase management</li>
                        <li><i class="bi bi-check-lg"></i> Email support</li>
                        <li><i class="bi bi-check-lg"></i> 1 GB storage</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-pricing">Get Started</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card featured">
                    <div class="pricing-badge">Most Popular</div>
                    <div class="plan-name">Professional</div>
                    <div class="plan-desc">For growing teams with advanced needs</div>
                    <div class="price">$79 <span>/month</span></div>
                    <ul class="features-list">
                        <li><i class="bi bi-check-lg"></i> Up to 50 users</li>
                        <li><i class="bi bi-check-lg"></i> Full POS &amp; Sales suite</li>
                        <li><i class="bi bi-check-lg"></i> Advanced inventory control</li>
                        <li><i class="bi bi-check-lg"></i> Accounting &amp; Finance</li>
                        <li><i class="bi bi-check-lg"></i> HR &amp; Payroll</li>
                        <li><i class="bi bi-check-lg"></i> Reports &amp; Analytics</li>
                        <li><i class="bi bi-check-lg"></i> Priority support</li>
                        <li><i class="bi bi-check-lg"></i> 10 GB storage</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-pricing">Get Started</a>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <div class="plan-name">Enterprise</div>
                    <div class="plan-desc">For large organizations with custom needs</div>
                    <div class="price">$199 <span>/month</span></div>
                    <ul class="features-list">
                        <li><i class="bi bi-check-lg"></i> Unlimited users</li>
                        <li><i class="bi bi-check-lg"></i> All modules included</li>
                        <li><i class="bi bi-check-lg"></i> Custom integrations</li>
                        <li><i class="bi bi-check-lg"></i> Dedicated account manager</li>
                        <li><i class="bi bi-check-lg"></i> 24/7 phone &amp; email support</li>
                        <li><i class="bi bi-check-lg"></i> Unlimited storage</li>
                        <li><i class="bi bi-check-lg"></i> SLA guarantee</li>
                        <li><i class="bi bi-check-lg"></i> On-premise option</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn-pricing">Get Started</a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ Footer ════════════════════════════════════════════════ --}}
<footer class="landing-footer">
    <div class="container d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div>
            <span class="brand-name">{{ $name }}</span> &copy; {{ date('Y') }}. All rights reserved.
        </div>
        <div class="d-flex gap-3">
            <span class="brand-name">v{{ app()->version() }}</span>
        </div>
    </div>
</footer>

</body>
</html>
