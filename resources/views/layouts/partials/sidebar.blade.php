<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        @php
            $logo = \App\Models\Setting::getValue('company_logo');
            $name = \App\Models\Setting::getValue('company_name', 'ERP');
        @endphp
        @if(!empty($logo))
            <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="brand-logo">
        @else
            <div class="brand-icon">{{ strtoupper(substr($name, 0, 1)) }}</div>
        @endif
        <span class="brand-name">{{ $name }}</span>
    </a>

    <div class="sidebar-nav">
        <ul class="nav flex-column p-0">

            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-grid-1x2"></i> Dashboard
                </a>
            </li>

            <li class="nav-label">POS</li>

            <li class="nav-item has-sub {{ request()->routeIs('pos.*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <i class="bi bi-cart2"></i> POS
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'active' : '' }}">Open POS</a></li>
                    <li><a href="{{ route('pos.list') }}" class="nav-link {{ request()->routeIs('pos.list') ? 'active' : '' }}">Transactions</a></li>
                </ul>
            </li>

            <li class="nav-label">Sales & Customers</li>

            <li class="nav-item has-sub {{ request()->routeIs('sale*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('sale*') ? 'active' : '' }}">
                    <i class="bi bi-cart-check"></i> Sales
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('sale.create') }}" class="nav-link {{ request()->routeIs('sale.create') ? 'active' : '' }}">New Sale</a></li>
                    <li><a href="{{ route('sale.index') }}" class="nav-link {{ request()->routeIs('sale.index') ? 'active' : '' }}">Sales List</a></li>
                    <li><a href="{{ route('sale-returns.create') }}" class="nav-link {{ request()->routeIs('sale-returns.create') ? 'active' : '' }}">Sale Return</a></li>
                    <li><a href="{{ route('sale-returns.index') }}" class="nav-link {{ request()->routeIs('sale-returns.index','sale-returns.show') ? 'active' : '' }}">Sale Returns</a></li>
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('customers*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('customers*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Customers
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.index') ? 'active' : '' }}">Customer List</a></li>
                    <li><a href="{{ route('customers.create') }}" class="nav-link {{ request()->routeIs('customers.create') ? 'active' : '' }}">Add Customer</a></li>
                    <li><a href="{{ route('customers.ledger') }}" class="nav-link {{ request()->routeIs('customers.ledger') ? 'active' : '' }}">Ledger</a></li>
                    <li><a href="{{ route('customers.credit') }}" class="nav-link {{ request()->routeIs('customers.credit') ? 'active' : '' }}">Credit Customers</a></li>
                </ul>
            </li>

            <li class="nav-label">Purchases & Suppliers</li>

            <li class="nav-item has-sub {{ request()->routeIs('purchase*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('purchase*') ? 'active' : '' }}">
                    <i class="bi bi-cart-plus"></i> Purchase
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('purchase.create') }}" class="nav-link {{ request()->routeIs('purchase.create') ? 'active' : '' }}">Add Purchase</a></li>
                    <li><a href="{{ route('purchase.index') }}" class="nav-link {{ request()->routeIs('purchase.index') ? 'active' : '' }}">Purchase List</a></li>
                    <li><a href="{{ route('purchase-returns.create') }}" class="nav-link {{ request()->routeIs('purchase-returns.create') ? 'active' : '' }}">Purchase Return</a></li>
                    <li><a href="{{ route('purchase-returns.index') }}" class="nav-link {{ request()->routeIs('purchase-returns.index','purchase-returns.show') ? 'active' : '' }}">Purchase Returns</a></li>
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('supplier*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('supplier*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> Suppliers
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('supplier.index') }}" class="nav-link {{ request()->routeIs('supplier.index') ? 'active' : '' }}">Supplier List</a></li>
                    <li><a href="{{ route('supplier.create') }}" class="nav-link {{ request()->routeIs('supplier.create') ? 'active' : '' }}">Add Supplier</a></li>
                    <li><a href="{{ route('supplier.ledger') }}" class="nav-link {{ request()->routeIs('supplier.ledger') ? 'active' : '' }}">Supplier Ledger</a></li>
                </ul>
            </li>

            <li class="nav-label">Products & Inventory</li>

            <li class="nav-item has-sub {{ request()->routeIs('unit*','category*','product*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('unit*','category*','product*') ? 'active' : '' }}">
                    <i class="bi bi-box"></i> Products
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

            <li class="nav-item">
                <a href="{{ route('stock.index') }}" class="nav-link text-white {{ request()->routeIs('stock*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Stock
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('near-to-expiry.index') }}" class="nav-link text-white {{ request()->routeIs('near-to-expiry*') ? 'active' : '' }}">
                    <i class="bi bi-exclamation-triangle"></i> Near to Expiry
                    @php
                        $nearExpiryCount = DB::table('product_batches')
                            ->join('products', 'product_batches.product_id', '=', 'products.id')
                            ->where('product_batches.quantity', '>', 0)
                            ->where('products.expiry_alert_days', '>', 0)
                            ->whereRaw('DATEDIFF(product_batches.expiry_date, CURDATE()) BETWEEN 0 AND products.expiry_alert_days')
                            ->count();
                    @endphp
                    @if($nearExpiryCount > 0)
                        <span class="badge rounded-pill bg-danger ms-auto">{{ $nearExpiryCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-label">Accounting & Finance</li>

            <li class="nav-item">
                <a href="{{ route('chart-of-accounts.index') }}" class="nav-link text-white {{ request()->routeIs('chart-of-accounts*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i> Chart of Accounts
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('opening-balances.index') }}" class="nav-link text-white {{ request()->routeIs('opening-balances*') ? 'active' : '' }}">
                    <i class="bi bi-cash-stack"></i> Opening Balances
                </a>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('bank*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('bank*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Bank Accounts
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('bank.index') }}" class="nav-link {{ request()->routeIs('bank.index') ? 'active' : '' }}">Bank Account List</a></li>
                    <li><a href="{{ route('bank.create') }}" class="nav-link {{ request()->routeIs('bank.create') ? 'active' : '' }}">Add Bank Account</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="{{ route('expenses.index') }}" class="nav-link text-white {{ request()->routeIs('expenses*') ? 'active' : '' }}">
                    <i class="bi bi-wallet"></i> Expenses
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('cash-adjustments.index') }}" class="nav-link text-white {{ request()->routeIs('cash-adjustments*') ? 'active' : '' }}">
                    <i class="bi bi-percent"></i> Cash Adjustments
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('customer-payments.index') }}" class="nav-link text-white {{ request()->routeIs('customer-payments*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i> Customer Payments
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('supplier-payments.index') }}" class="nav-link text-white {{ request()->routeIs('supplier-payments*') ? 'active' : '' }}">
                    <i class="bi bi-card-text"></i> Supplier Payments
                </a>
            </li>

            <li class="nav-label">Reports</li>

            <li class="nav-item has-sub {{ request()->routeIs('cashbook*','bank-book*','inventory-ledger*','general-ledgers*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('cashbook*','bank-book*','inventory-ledger*','general-ledgers*') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Financial Ledgers
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('cashbook.index') }}" class="nav-link {{ request()->routeIs('cashbook*') ? 'active' : '' }}">Cashbook</a></li>
                    <li><a href="{{ route('bank-book.index') }}" class="nav-link {{ request()->routeIs('bank-book*') ? 'active' : '' }}">Bank Book</a></li>
                    <li><a href="{{ route('general-ledgers.index') }}" class="nav-link {{ request()->routeIs('general-ledgers*') ? 'active' : '' }}">General Ledger</a></li>
                    <li><a href="{{ route('inventory-ledger.index') }}" class="nav-link {{ request()->routeIs('inventory-ledger*') ? 'active' : '' }}">Inventory Ledger</a></li>
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('profit-loss*','balance-sheet*','cash-flow*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('profit-loss*','balance-sheet*','cash-flow*') ? 'active' : '' }}">
                    <i class="bi bi-file-spreadsheet"></i> Financial Statements
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('profit-loss.index') }}" class="nav-link {{ request()->routeIs('profit-loss*') ? 'active' : '' }}">Profit & Loss</a></li>
                    <li><a href="{{ route('balance-sheet.index') }}" class="nav-link {{ request()->routeIs('balance-sheet*') ? 'active' : '' }}">Balance Sheet</a></li>
                    <li><a href="{{ route('cash-flow.index') }}" class="nav-link {{ request()->routeIs('cash-flow*') ? 'active' : '' }}">Cash Flow</a></li>
                </ul>
            </li>

            <li class="nav-item has-sub {{ request()->routeIs('daily-closing*','closing-report*','today-report*','sale-report*','due-report*','purchase-report*') ? 'open' : '' }}">
                <a href="#" class="nav-link text-white {{ request()->routeIs('daily-closing*','closing-report*','today-report*','sale-report*','due-report*','purchase-report*') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line"></i> Operational Reports
                </a>
                <ul class="sub-menu">
                    <li><a href="{{ route('daily-closing.index') }}" class="nav-link {{ request()->routeIs('daily-closing*') ? 'active' : '' }}">Daily Closing</a></li>
                    <li><a href="{{ route('closing-report.index') }}" class="nav-link {{ request()->routeIs('closing-report*') ? 'active' : '' }}">Closing Report</a></li>
                    <li><a href="{{ route('today-report.index') }}" class="nav-link {{ request()->routeIs('today-report*') ? 'active' : '' }}">Today Report</a></li>
                    <li><a href="{{ route('sale-report.index') }}" class="nav-link {{ request()->routeIs('sale-report*') ? 'active' : '' }}">Sale Report</a></li>
                    <li><a href="{{ route('purchase-report.index') }}" class="nav-link {{ request()->routeIs('purchase-report*') ? 'active' : '' }}">Purchase Report</a></li>
                    <li><a href="{{ route('due-report.index') }}" class="nav-link {{ request()->routeIs('due-report*') ? 'active' : '' }}">Due Report</a></li>
                </ul>
            </li>

            <li class="nav-label">HR</li>

            <li class="nav-item">
                <a href="{{ route('employees.index') }}" class="nav-link text-white {{ request()->routeIs('employees*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> Employees
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('salary.index') }}" class="nav-link text-white {{ request()->routeIs('salary*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i> Salaries
                    @php
                        $currentMonth = now()->format('Y-m');
                        $paidIds = DB::table('salary_payments')
                            ->where('salary_month', $currentMonth)
                            ->pluck('employee_id');
                        $pendingSalaryCount = DB::table('employees')
                            ->where('status', true)
                            ->whereNotIn('id', $paidIds)
                            ->count();
                    @endphp
                    @if($pendingSalaryCount > 0)
                        <span class="badge rounded-pill bg-warning text-dark ms-auto">{{ $pendingSalaryCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-label">System</li>

            <li class="nav-item">
                <a href="{{ route('settings.index') }}" class="nav-link text-white {{ request()->routeIs('settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link text-white {{ request()->routeIs('users*') ? 'active' : '' }}">
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
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="{{ route('settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
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
