<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="brand-icon">E</div>
        <span class="brand-name">ERP<span>Pro</span></span>
    </a>

    <div class="sidebar-nav">
        <ul class="nav flex-column p-0">

            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>

            <li class="nav-label">Sales</li>

            <li class="nav-item has-sub {{ request()->routeIs('unit*','category*','product*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('unit*','category*','product*') ? 'active' : '' }}">
                    <x-lucide-box style="width: 20px; height: 20px;" /> Products
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('unit.unitlist') }}" class="nav-link {{ request()->routeIs('unit.unitlist') ? 'active' : '' }}">Unit List</a></li>
                    <li><a href="{{ route('unit.create') }}" class="nav-link {{ request()->routeIs('unit.create') ? 'active' : '' }}">Add Unit</a></li>
                    <li><a href="{{ route('category.create') }}" class="nav-link {{ request()->routeIs('category.create') ? 'active' : '' }}">Add Category</a></li>
                    <li><a href="{{ route('category.index') }}" class="nav-link {{ request()->routeIs('category.index') ? 'active' : '' }}">Category List</a></li>
                    <li><a href="{{ route('product.create') }}" class="nav-link {{ request()->routeIs('product.create') ? 'active' : '' }}">Add Product</a></li>
                    <li><a href="{{ route('product.index') }}" class="nav-link {{ request()->routeIs('product.index') ? 'active' : '' }}">Products List</a></li>
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('supplier*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('supplier*') ? 'active' : '' }}">
                    <x-lucide-handshake style="width: 20px; height: 20px;" /> Suppliers
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('supplier.index') }}" class="nav-link {{ request()->routeIs('supplier.index') ? 'active' : '' }}">Supplier List</a></li>
                    <li><a href="{{ route('supplier.create') }}" class="nav-link {{ request()->routeIs('supplier.create') ? 'active' : '' }}">Add Supplier</a></li>
                    
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('bank*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('bank*') ? 'active' : '' }}">
                    <x-lucide-building-2 style="width: 20px; height: 20px;" /> Bank Accounts
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('bank.index') }}" class="nav-link {{ request()->routeIs('bank.index') ? 'active' : '' }}">Bank Account List</a></li>
                    <li><a href="{{ route('bank.create') }}" class="nav-link {{ request()->routeIs('bank.create') ? 'active' : '' }}">Add Bank Account</a></li>
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('purchase*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('purchase*') ? 'active' : '' }}">
                    <x-lucide-shopping-cart style="width: 20px; height: 20px;" /> Purchase
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('purchase.create') }}" class="nav-link {{ request()->routeIs('purchase.create') ? 'active' : '' }}">Add Purchase</a></li>
                    <li><a href="{{ route('purchase.index') }}" class="nav-link {{ request()->routeIs('purchase.index') ? 'active' : '' }}">Purchase List</a></li>
                    
                </ul>
            </li>

            <li class="nav-item has-sub  {{ request()->routeIs('invoices*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white">
                    <i class="bi bi-receipt"></i> Invoices
                    <span class="badge-pill">5</span>
                </a>
                <ul class="sub-menu">
                    <li><a href="#" class="nav-link">All Invoices</a></li>
                    <li><a href="#" class="nav-link">Create Invoice</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link text-white">
                    <i class="bi bi-bag-check"></i> Orders
                </a>
            </li>

            <li class="nav-label">Inventory</li>

            <li class="nav-item">
                <a href="#" class="nav-link text-white">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-building"></i> Suppliers
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-arrow-left-right"></i> Stock Movements
                </a>
            </li>

            <li class="nav-label">Finance</li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-wallet2"></i> Accounts
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-graph-up-arrow"></i> Reports
                </a>
            </li>

            <li class="nav-label">HR</li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-person-badge"></i> Employees
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-calendar3"></i> Attendance
                </a>
            </li>

            <li class="nav-label">System</li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="bi bi-people-fill"></i> User Management
                </a>
            </li>

        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="dropdown">
            <div class="sidebar-user" data-bs-toggle="dropdown">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'Admin', 0, 2)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'Admin User' }}</div>
                    <div class="user-role">{{ auth()->user()->role ?? 'Administrator' }}</div>
                </div>
                <i class="bi bi-three-dots-vertical" style="color:rgba(168,178,200,.6); font-size:14px;"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
