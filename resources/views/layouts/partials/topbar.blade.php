<header id="topbar" class="pt-2">
    <button class="topbar-toggler" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </button>

    <div>
        <div class="page-title">@yield('page-title', 'Dashboard')</div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">Home</a></li>
                @yield('breadcrumb')
            </ol>
        </nav>
    </div>

    <div class="topbar-right">
        <button class="topbar-btn" title="Search">
            <i class="bi bi-search"></i>
        </button>

        <button class="topbar-btn" title="Notifications">
            <i class="bi bi-bell"></i>
            <span class="dot"></span>
        </button>

        <button class="topbar-btn" title="Messages">
            <i class="bi bi-chat-dots"></i>
        </button>

        <div class="topbar-divider"></div>

        <div class="dropdown">
            <button class="topbar-btn" data-bs-toggle="dropdown">
                <i class="bi bi-grid"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-bar-chart me-2"></i>Reports</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
            </ul>
        </div>
    </div>
</header>