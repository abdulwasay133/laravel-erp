@extends('layouts.app')

@section('title', 'POS')
@section('page-title', 'Point of Sale')

@section('breadcrumb')
    <li class="breadcrumb-item active">POS</li>
@endsection

@push('styles')
<style>
    #pos-wrapper { display: flex; gap: 0; height: calc(100vh - var(--topbar-height, 60px) - 8px); min-height: 500px; border-radius: 10px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    #pos-left { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: #fff; }
    #pos-right { width: 360px; display: flex; flex-direction: column; background: #f8fafc; border-left: 1px solid #e2e8f0; }

    .pos-topbar { display: flex; align-items: center; gap: 10px; padding: 8px 16px; background: #fff; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
    .pos-topbar .form-control { border-radius: 8px; }
    .pos-cats { padding: 6px 16px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; gap: 4px; max-height: 60px; overflow-y: auto; flex-shrink: 0; }
    .pos-cats .cat-pill { padding: 4px 14px; border-radius: 20px; border: 1px solid #e2e8f0; font-size: 12px; cursor: pointer; background: #fff; transition: all .15s; white-space: nowrap; font-weight: 500; color: #64748b; }
    .pos-cats .cat-pill:hover { border-color: #85D1DB; color: #1E3A4C; background: rgba(133, 209, 219, 0.08); }
    .pos-cats .cat-pill.active { background: #85D1DB; color: #1E3A4C; border-color: #85D1DB; font-weight: 600; }
    .pos-products { flex: 1; overflow-y: auto; padding: 12px; display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; align-content: start; }
    .pos-product-btn { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 8px; text-align: center; cursor: pointer; background: #fff; transition: all .18s; display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .pos-product-btn:hover { border-color: #85D1DB; box-shadow: 0 4px 12px rgba(133, 209, 219, 0.15); transform: translateY(-2px); }
    .pos-product-btn .name { font-size: 13px; font-weight: 600; line-height: 1.3; color: #1e293b; }
    .pos-product-btn .price { font-size: 15px; font-weight: 700; color: #1E3A4C; }
    .pos-product-btn .stock { font-size: 11px; color: #94a3b8; font-weight: 500; }
    .pos-product-btn.out-of-stock { opacity: .45; pointer-events: none; }

    .cart-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; background: #fff; }
    .cart-header .cart-title { font-size: 14px; font-weight: 700; color: #1e293b; }
    .cart-items { flex: 1; overflow-y: auto; padding: 6px 0; min-height: 60px; }
    .cart-item { display: flex; align-items: center; gap: 12px; padding: 14px 20px; border-bottom: 1px solid #f1f5f9; transition: background .1s; }
    .cart-item:hover { background: #f8fafc; }
    .cart-item .item-info { flex: 1; min-width: 0; }
    .cart-item .item-name { font-size: 14px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cart-item .item-price { font-size: 12px; color: #94a3b8; }
    .cart-item .qty-ctrl { display: flex; align-items: center; gap: 3px; flex-shrink: 0; }
    .cart-item .qty-ctrl button { width: 28px; height: 28px; padding: 0; font-size: 15px; line-height: 1; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #64748b; transition: all .12s; }
    .cart-item .qty-ctrl button:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .cart-item .qty-ctrl input { width: 36px; text-align: center; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 13px; padding: 2px; font-weight: 600; }
    .cart-item .line-total { font-size: 15px; font-weight: 700; min-width: 80px; text-align: right; flex-shrink: 0; color: #1e293b; }
    .cart-item .remove-btn { color: #94a3b8; cursor: pointer; font-size: 18px; padding: 4px; flex-shrink: 0; transition: color .12s; line-height: 1; }
    .cart-item .remove-btn:hover { color: #ef4444; }

    .cart-footer { padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #fff; display: flex; flex-direction: column; gap: 8px; flex-shrink: 0; }
    .cart-footer .total-row { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
    .cart-footer .total-row .label { color: #64748b; font-weight: 500; }
    .cart-footer .total-row .value { font-weight: 600; color: #1e293b; }
    .cart-footer .grand-total-row { font-size: 18px; font-weight: 800; color: #1E3A4C; border-top: 2px solid #85D1DB; padding-top: 8px; margin-top: 4px; }
    .cart-footer .discount-row input { width: 70px; text-align: right; border-radius: 6px; }

    .pos-payment { padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #fff; flex-shrink: 0; display: flex; flex-direction: column; gap: 10px; }
    .pos-payment .payment-type-btn.active { background: #85D1DB; color: #1E3A4C; border-color: #85D1DB; font-weight: 600; }
    .pos-payment .payment-type-btn:not(.active) { border-color: #e2e8f0; color: #64748b; }
    .pos-payment .payment-method-btn.active { background: #85D1DB; color: #1E3A4C; border-color: #85D1DB; font-weight: 600; }
    .pos-payment .payment-method-btn:not(.active) { border-color: #e2e8f0; color: #64748b; }

    #sessionBar { display: flex; justify-content: space-between; align-items: center; padding: 8px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 13px; flex-shrink: 0; border-radius: 10px 10px 0 0; }
    #sessionBar .session-open { color: #10b981; font-weight: 600; }
    .customer-badge { font-size: 13px; cursor: pointer; color: #85D1DB; font-weight: 600; }
    .customer-badge:hover { color: #1E3A4C; }
    .pos-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; height: 100%; gap: 8px; padding: 40px; flex: 1; }
    .pos-empty i { font-size: 48px; }
    .fw-700 { font-weight: 700; }
    .barcode-input { font-family: 'Courier New', monospace; letter-spacing: 2px; }
</style>
@endpush

@section('content')
<div style="margin: -24px;">
    {{-- Session Bar --}}
    <div id="sessionBar">
        <span id="sessionStatus"><i class="bi bi-hourglass-split me-1"></i> Loading session...</span>
        <div class="d-flex align-items-center gap-3">
            <span class="customer-badge text-decoration-none" id="customerBadge" data-bs-toggle="modal" data-bs-target="#customerModal">
                <i class="bi bi-person-circle me-1"></i> <span id="customerName">Walk-in</span>
            </span>
            <button class="btn btn-sm btn-outline-secondary" id="resumeBtn" style="display:none;" title="Resume suspended cart"><i class="bi bi-play-fill me-1"></i>Resume</button>
            <a href="#" class="text-muted text-decoration-none" id="shortcutHelp" title="Keyboard shortcuts"><i class="bi bi-keyboard"></i></a>
        </div>
    </div>

    {{-- POS Layout --}}
    <div id="pos-wrapper">

        {{-- LEFT: Products --}}
        <div id="pos-left">
            {{-- Row 1: Barcode / Search --}}
            <div class="pos-topbar">
                <div class="input-group" style="max-width:400px;">
                    <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                    <input type="text" id="barcodeInput" class="form-control barcode-input border-start-0 ps-0" placeholder="Scan barcode or search..." autofocus>
                    <button class="btn btn-primary" id="searchBtn"><i class="bi bi-search"></i></button>
                </div>
                <input type="text" id="productSearchInput" class="form-control" placeholder="Search products..." autocomplete="off" style="max-width:240px;">
            </div>

            {{-- Row 2: Category Pills --}}
            <div class="pos-cats" id="categoryPills">
                <span class="cat-pill active" data-id="">All</span>
            </div>

            {{-- Row 3: Product Grid --}}
            <div class="pos-products" id="productGrid">
                <div class="pos-empty"><i class="bi bi-box-seam"></i><span>Open a session to start selling</span></div>
            </div>
        </div>

        {{-- RIGHT: Cart + Payment --}}
        <div id="pos-right">
            <div class="cart-header">
                <span class="cart-title"><i class="bi bi-cart3 me-1"></i> Cart <span class="text-muted fw-normal" id="cartCount">(0)</span></span>
                <button class="btn btn-sm btn-outline-danger" id="clearCartBtn" title="Clear cart"><i class="bi bi-trash3 me-1"></i>Clear</button>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="pos-empty"><i class="bi bi-cart"></i><span>Cart is empty</span></div>
            </div>

            {{-- Cart Footer --}}
            <div class="cart-footer" id="cartTotals" style="display:none;">
                <div class="total-row">
                    <span class="label">Subtotal</span>
                    <span class="value" id="subtotal">Rs. 0</span>
                </div>
                <div class="total-row discount-row">
                    <span class="label">Discount <input type="number" id="discountInput" class="form-control form-control-sm d-inline-block ms-1" value="0" min="0" step="1"></span>
                    <span class="value" id="discountDisplay" style="color:#ef4444;">- Rs. 0</span>
                </div>
                <div class="total-row grand-total-row">
                    <span>Total Due</span>
                    <span id="dueAmount">Rs. 0</span>
                </div>
            </div>

            {{-- Payment Section --}}
            <div class="pos-payment" id="paymentSection" style="display:none;">
                <div class="btn-group btn-group-sm w-100" id="paymentTypeGroup">
                    <label class="btn payment-type-btn active">
                        <input type="radio" name="paymentType" value="full" checked hidden> <i class="bi bi-check-circle me-1"></i>Full Amount
                    </label>
                    <label class="btn payment-type-btn">
                        <input type="radio" name="paymentType" value="custom" hidden> <i class="bi bi-pencil me-1"></i>Custom
                    </label>
                </div>

                <div class="row g-2 align-items-center">
                    <div class="col-7">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rs.</span>
                            <input type="number" class="form-control" id="tenderedInput" step="1" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="d-flex align-items-center gap-1">
                            <small class="text-muted">Change:</small>
                            <span class="fw-700" id="changeDisplay">Rs. 0</span>
                        </div>
                    </div>
                </div>

                <div id="shortfallAlert" class="alert alert-warning py-1 px-2 small mb-0" style="display:none;">
                    <i class="bi bi-exclamation-triangle me-1"></i> Remaining <span id="shortfallAmount">Rs. 0</span> as credit
                </div>

                <div class="d-flex gap-2">
                    <div class="btn-group btn-group-sm flex-shrink-0" id="paymentMethodGroup">
                        <label class="btn payment-method-btn active">
                            <input type="radio" name="paymentMethod" value="cash" checked hidden> <i class="bi bi-cash me-1"></i>Cash
                        </label>
                        <label class="btn payment-method-btn">
                            <input type="radio" name="paymentMethod" value="bank" hidden> <i class="bi bi-bank me-1"></i>Bank
                        </label>
                    </div>
                    <button class="btn btn-success flex-fill" id="completePaymentBtn"><i class="bi bi-check-lg me-1"></i> Complete Sale</button>
                    <button class="btn btn-outline-secondary" id="holdBtn" title="Hold cart"><i class="bi bi-pause"></i></button>
                </div>

                <div id="bankAccountGroup" style="display:none;">
                    <select class="form-select form-select-sm" id="bankAccountSelect">
                        <option value="">Select bank account...</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Customer Modal --}}
<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Select Customer</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="text" class="form-control mb-2" id="customerSearchInput" placeholder="Search by name or phone...">
                <div class="list-group" id="customerList" style="max-height:250px;overflow-y:auto;">
                    <a href="#" class="list-group-item list-group-item-action" data-id="">Walk-in Customer</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Hold Resume Modal --}}
<div class="modal fade" id="holdModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title">Suspended Carts</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="holdList"></div>
        </div>
    </div>
</div>

{{-- Shortcuts Modal --}}
<div class="modal fade" id="shortcutsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header"><h6 class="modal-title"><i class="bi bi-keyboard me-1"></i> Keyboard Shortcuts</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr><td><kbd>F2</kbd></td><td>Select Customer</td></tr>
                        <tr><td><kbd>F3</kbd></td><td>Focus barcode scanner</td></tr>
                        <tr><td><kbd>F4</kbd></td><td>Edit last item quantity</td></tr>
                        <tr><td><kbd>F6</kbd></td><td>Focus Discount</td></tr>
                        <tr><td><kbd>F7</kbd></td><td>Full Amount</td></tr>
                        <tr><td><kbd>F8</kbd></td><td>Custom Amount</td></tr>
                        <tr><td><kbd>F9</kbd></td><td>Cash payment</td></tr>
                        <tr><td><kbd>F10</kbd></td><td>Bank payment</td></tr>
                        <tr><td><kbd>F12</kbd> / <kbd>Ctrl</kbd> + <kbd>Enter</kbd></td><td>Complete Sale</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Success Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="successToast" class="toast align-items-center text-bg-success border-0" role="alert">
        <div class="d-flex"><div class="toast-body" id="toastMessage">Sale completed!</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // ── State ────────────────────────────────────────────
    let cart = [];
    let currentSession = null;
    let selectedCustomerId = null;
    let selectedCustomerName = 'Walk-in';
    let products = [];
    let bankAccounts = [];
    let categories = [];
    let activeCategoryId = '';
    let searchQuery = '';
    let searchTimer = null;

    // ── Session Management ───────────────────────────────
    function loadSession() {
        $.get('/api/pos/session/current', function (res) {
            if (res.session) {
                currentSession = res.session;
                $('#sessionStatus').html('<i class="bi bi-check-circle text-success me-1"></i> Session #' + res.session.id + ' — <span class="session-open">Open</span>');
                loadProducts();
                loadBankAccounts();
                loadHolds();
            } else {
                $('#sessionStatus').html('<i class="bi bi-exclamation-circle text-warning me-1"></i> No active session');
                $('#productGrid').html('<div class="pos-empty"><i class="bi bi-shop"></i><span>Open a session to start</span><button class="btn btn-primary btn-sm mt-2" id="openSessionBtn"><i class="bi bi-play me-1"></i>Open Session</button></div>');
                clearCart();
            }
        });
    }

    $(document).on('click', '#openSessionBtn', function () {
        Swal.fire({
            title: 'Open Session',
            input: 'number',
            inputLabel: 'Opening Balance (Rs.)',
            inputValue: '0',
            showCancelButton: true,
            confirmButtonText: 'Open',
            confirmButtonColor: '#0d6efd',
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/api/pos/session/open', { opening_balance: result.value || 0 })
                    .done(function (res) {
                        showToast('Session #' + res.session.id + ' opened!');
                        loadSession();
                    })
                    .fail(function (xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Failed to open session', 'error'); });
            }
        });
    });

    // ── Categories ────────────────────────────────────────
    function loadCategories() {
        $.get('/api/pos/categories', function (res) {
            categories = res.categories || [];
            let html = '<span class="cat-pill active" data-id="">All</span>';
            categories.forEach(c => html += '<span class="cat-pill" data-id="' + c.id + '">' + $('<span>').text(c.name).html() + '</span>');
            $('#categoryPills').html(html);
        });
    }

    $(document).on('click', '.cat-pill', function () {
        $('.cat-pill').removeClass('active');
        $(this).addClass('active');
        activeCategoryId = $(this).data('id') || '';
        loadProducts();
    });

    // ── Products ─────────────────────────────────────────
    function loadProducts() {
        $('#productGrid').html('<div class="pos-empty"><i class="bi bi-arrow-repeat"></i><span>Loading products...</span></div>');
        let url = '/api/pos/products?';
        const params = [];
        if (activeCategoryId) params.push('category_id=' + activeCategoryId);
        if (searchQuery) params.push('search=' + encodeURIComponent(searchQuery));
        url += params.join('&');
        $.get(url, function (res) {
            products = res.data || [];
            renderProducts();
        });
    }

    function renderProducts() {
        if (!products.length) {
            $('#productGrid').html('<div class="pos-empty"><i class="bi bi-box-seam"></i><span>No products found</span></div>');
            return;
        }
        let html = '';
        products.forEach(p => {
            const hasStock = p.stock > 0;
            html += '<div class="pos-product-btn' + (hasStock ? '' : ' out-of-stock') + '" data-id="' + p.id + '" data-name="' + $('<span>').text(p.name).html() + '" data-price="' + p.sale_price + '" data-barcode="' + (p.barcode || '') + '" data-sku="' + (p.sku || '') + '" data-batches=\'' + JSON.stringify(p.batches) + '\'>';
            html += '<div class="name">' + $('<span>').text(p.name).html() + '</div>';
            html += '<div class="price">Rs. ' + p.sale_price.toLocaleString() + '</div>';
            html += '<div class="stock">' + (p.stock > 0 ? p.stock + ' ' + p.unit : 'Out of stock') + '</div>';
            html += '</div>';
        });
        $('#productGrid').html(html);
    }

    // ── Product Name Search ──────────────────────────────
    $('#productSearchInput').on('input', function () {
        clearTimeout(searchTimer);
        searchQuery = $(this).val().trim();
        searchTimer = setTimeout(function () {
            if (searchQuery.length >= 1) {
                loadProducts();
            } else {
                loadProducts();
            }
        }, 300);
    });

    // ── Bank Accounts ────────────────────────────────────
    function loadBankAccounts() {
        $.get('/api/pos/bank-accounts', function (res) {
            bankAccounts = res.accounts || [];
            const sel = $('#bankAccountSelect');
            sel.empty().append('<option value="">Select bank account...</option>');
            bankAccounts.forEach(a => sel.append('<option value="' + a.id + '">' + a.bank_name + ' - ' + a.account_number + '</option>'));
        }).fail(() => {});
    }

    // ── Cart Logic ───────────────────────────────────────
    function getProductId(p) { return p.product_id || p.id; }

    function addToCart(product, batchId) {
        if (product.stock <= 0) return;
        const pid = getProductId(product);
        const existing = cart.find(c => c.product_id === pid && c.batch_id === (batchId || null));
        if (existing) {
            if (existing.quantity < product.stock) existing.quantity++;
        } else {
            cart.push({
                product_id: pid,
                batch_id: batchId || null,
                product_name: product.name,
                barcode: product.barcode || '',
                sku: product.sku || '',
                quantity: 1,
                unit_price: product.sale_price,
                discount_amount: 0,
                stock: product.stock,
                cost: product.batches?.[0]?.cost || 0,
            });
        }
        renderCart();
        $('#barcodeInput').val('').focus();
    }

    function getCartDiscount() {
        return parseFloat($('#discountInput').val()) || 0;
    }

    function renderCart() {
        const container = $('#cartItems');
        if (!cart.length) {
            container.html('<div class="pos-empty"><i class="bi bi-cart"></i><span>Cart is empty</span></div>');
            $('#cartTotals, #paymentSection').hide();
            $('#cartCount').text('0');
            return;
        }
        let html = '';
        let subtotal = 0;
        cart.forEach((item, idx) => {
            const lineTotal = (item.unit_price * item.quantity) - (item.discount_amount || 0);
            subtotal += item.unit_price * item.quantity;
            html += '<div class="cart-item" data-index="' + idx + '">';
            html += '<div class="item-info">';
            html += '<div class="item-name">' + $('<span>').text(item.product_name).html() + '</div>';
            html += '<div class="item-price">' + item.barcode + '</div>';
            html += '</div>';
            html += '<div class="qty-ctrl">';
            html += '<button class="qty-down">\u2212</button>';
            html += '<input type="number" class="qty-input" value="' + item.quantity + '" min="1" max="' + item.stock + '">';
            html += '<button class="qty-up">+</button>';
            html += '</div>';
            html += '<div class="line-total">Rs. ' + lineTotal.toLocaleString() + '</div>';
            html += '<span class="remove-btn">&times;</span>';
            html += '</div>';
        });
        container.html(html);

        $('#cartCount').text(cart.length);
        const discount = getCartDiscount();
        const grandTotal = Math.max(0, subtotal - discount);
        $('#subtotal').text('Rs. ' + subtotal.toLocaleString());
        $('#discountDisplay').text('- Rs. ' + discount.toLocaleString());
        $('#dueAmount').text('Rs. ' + grandTotal.toLocaleString()).data('value', grandTotal);
        $('#cartTotals, #paymentSection').show();

        if ($('#paymentTypeGroup .active input').val() === 'full') {
            applyFullAmount();
        }
        updateTenderedChange();
    }

    function clearCart() {
        cart = [];
        $('#discountInput').val(0);
        renderCart();
    }

    $('#discountInput').on('input', function () {
        renderCart();
    });

    // ── Cart Events ──────────────────────────────────────
    $(document).on('click', '.pos-product-btn:not(.out-of-stock)', function () {
        const batches = $(this).data('batches') || [];
        const batchId = batches.length > 0 ? batches[0].id : null;
        addToCart({
            id: $(this).data('id'),
            name: $(this).data('name'),
            sale_price: $(this).data('price'),
            barcode: $(this).data('barcode'),
            sku: $(this).data('sku'),
            stock: $(this).find('.stock').text().split(' ')[0],
            batches: batches,
        }, batchId);
    });

    $(document).on('click', '.cart-item .remove-btn', function () {
        const idx = $(this).closest('.cart-item').data('index');
        cart.splice(idx, 1);
        renderCart();
    });

    $(document).on('click', '.qty-up', function () {
        const idx = $(this).closest('.cart-item').data('index');
        const item = cart[idx];
        if (item.quantity < item.stock) { item.quantity++; renderCart(); }
    });

    $(document).on('click', '.qty-down', function () {
        const idx = $(this).closest('.cart-item').data('index');
        const item = cart[idx];
        if (item.quantity > 1) { item.quantity--; renderCart(); }
        else { cart.splice(idx, 1); renderCart(); }
    });

    $(document).on('change', '.qty-input', function () {
        const idx = $(this).closest('.cart-item').data('index');
        const val = parseFloat($(this).val());
        const item = cart[idx];
        if (val > 0 && val <= item.stock) { item.quantity = val; renderCart(); }
        else { $(this).val(item.quantity); }
    });

    $('#clearCartBtn').on('click', function () {
        if (cart.length) Swal.fire({ title: 'Clear cart?', icon: 'question', showCancelButton: true, confirmButtonText: 'Yes' }).then(r => { if (r.isConfirmed) clearCart(); });
    });

    // ── Barcode / Search ─────────────────────────────────
    $('#barcodeInput').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const q = $(this).val().trim();
            if (!q) return;
            $.get('/api/pos/products/' + encodeURIComponent(q), function (res) {
                if (res.product) {
                    addToCart(res.product, res.product.batch_id);
                }
            }).fail(function () {
                $.get('/api/pos/products/search', { q: q }, function (res) {
                    if (res.products && res.products.length) {
                        addToCart(res.products[0], res.products[0].batch_id);
                    } else {
                        Swal.fire('Not found', 'No product matches "' + q + '"', 'warning');
                    }
                });
            });
        }
    });

    $('#searchBtn').on('click', function () {
        const q = $('#barcodeInput').val().trim();
        if (!q) return;
        $.get('/api/pos/products/search', { q: q }, function (res) {
            if (res.products && res.products.length) {
                if (res.products.length === 1) {
                    addToCart(res.products[0], res.products[0].batch_id);
                } else {
                    let opts = '';
                    res.products.forEach(p => { opts += '<a href="#" class="list-group-item list-group-item-action search-result" data-product=\'' + JSON.stringify(p) + '\'>' + $('<span>').text(p.name).html() + ' \u2014 Rs. ' + p.sale_price + '</a>'; });
                    Swal.fire({ title: 'Select Product', html: '<div class="list-group" style="max-height:300px;overflow-y:auto;">' + opts + '</div>', showConfirmButton: false, didOpen: () => {
                        $('.search-result').on('click', function (e) { e.preventDefault(); const p = $(this).data('product'); addToCart(p, p.batch_id); Swal.close(); });
                    }});
                }
            } else {
                Swal.fire('Not found', 'No products match "' + q + '"', 'warning');
            }
        });
    });

    // ── Customer Selection ───────────────────────────────
    $('#customerSearchInput').on('keyup', function () {
        const q = $(this).val();
        $.get('/api/pos/customers', { q: q }, function (res) {
            let html = '<a href="#" class="list-group-item list-group-item-action customer-option" data-id="" data-name="Walk-in">Walk-in Customer</a>';
            res.customers.forEach(c => {
                html += '<a href="#" class="list-group-item list-group-item-action customer-option" data-id="' + c.id + '" data-name="' + $('<span>').text(c.first_name + ' ' + (c.last_name || '')).html() + '" data-balance="' + (c.balance || 0) + '">' + $('<span>').text(c.first_name + ' ' + (c.last_name || '')).html() + ' \u2014 ' + (c.phone || '') + '</a>';
            });
            $('#customerList').html(html);
        });
    });

    $(document).on('click', '.customer-option', function (e) {
        e.preventDefault();
        selectedCustomerId = $(this).data('id') || null;
        selectedCustomerName = $(this).data('name') || 'Walk-in';
        $('#customerName').text(selectedCustomerName);
        $('#customerModal').modal('hide');
    });

    // ── Payment Logic (Inline) ───────────────────────────
    function updateTenderedChange() {
        const due = parseFloat($('#dueAmount').data('value')) || 0;
        const tendered = parseFloat($('#tenderedInput').val()) || 0;
        const diff = tendered - due;

        if (diff >= 0) {
            $('#changeDisplay').text('Rs. ' + diff.toLocaleString());
            $('#shortfallAlert').hide();
        } else {
            $('#changeDisplay').text('Rs. 0');
            if (selectedCustomerId) {
                const creditAmount = Math.abs(diff);
                $('#shortfallAlert').show().find('#shortfallAmount').text('Rs. ' + creditAmount.toLocaleString());
            } else {
                $('#shortfallAlert').hide();
            }
        }
    }

    function getDueFromCart() {
        const subtotal = cart.reduce((sum, c) => sum + (c.unit_price * c.quantity), 0);
        return Math.max(0, subtotal - getCartDiscount());
    }

    function applyFullAmount() {
        const due = getDueFromCart();
        $('#tenderedInput').val(due).prop('disabled', true).trigger('input');
    }

    $(document).on('change', '.payment-type-btn input', function () {
        $('.payment-type-btn').removeClass('active');
        $(this).closest('.payment-type-btn').addClass('active');
        if ($(this).val() === 'full') {
            applyFullAmount();
        } else {
            $('#tenderedInput').prop('disabled', false).focus();
        }
    });

    $('#tenderedInput').on('input', function () {
        updateTenderedChange();
    });

    // ── Payment Method Toggle ────────────────────────────
    $(document).on('click', '.payment-method-btn', function () {
        $('.payment-method-btn').removeClass('active');
        $(this).addClass('active');
        $('#bankAccountGroup').toggle($(this).find('input').val() === 'bank');
    });

    // ── Complete Sale ────────────────────────────────────
    $('#completePaymentBtn').on('click', function () {
        if (!cart.length) return;
        if (!currentSession || currentSession.status !== 'open') {
            Swal.fire('Session Required', 'Please open a POS session first.', 'warning');
            return;
        }

        const due = parseFloat($('#dueAmount').data('value')) || 0;
        const paid = parseFloat($('#tenderedInput').val()) || 0;
        const method = $('#paymentMethodGroup .active input').val();
        const bankAccountId = method === 'bank' ? $('#bankAccountSelect').val() : null;

        if (paid <= 0) {
            Swal.fire('Enter Amount', 'Please enter the paid amount or use Full Paid.', 'warning');
            return;
        }

        if (!selectedCustomerId && paid < due) {
            Swal.fire('Full Payment Required', 'Walk-in customers must pay the full amount.', 'warning');
            return;
        }

        if (paid < due) {
            const remainder = due - paid;
            Swal.fire({
                title: 'Partial Payment?',
                html: 'Remaining <strong>Rs. ' + remainder.toLocaleString() + '</strong> will be recorded as credit.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, create credit',
                cancelButtonText: 'Cancel',
            }).then(result => {
                if (result.isConfirmed) doPayment(due, paid, method, bankAccountId);
            });
            return;
        }

        if (paid > due && selectedCustomerId) {
            const excess = paid - due;
            Swal.fire({
                title: 'Excess Paid',
                html: 'Excess <strong>Rs. ' + excess.toLocaleString() + '</strong>. Store as customer credit?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, store as credit',
                cancelButtonText: 'No, give change',
            }).then(result => {
                if (result.isConfirmed) {
                    doPayment(due + excess, paid, method, bankAccountId, null, excess);
                } else {
                    doPayment(due, due, method, bankAccountId);
                }
            });
            return;
        }

        doPayment(due, paid, method, bankAccountId);
    });

    function doPayment(due, tendered, method, bankAccountId, notes, excessAsCredit) {
        const discount = getCartDiscount();
        const payments = [];

        if (tendered > 0 && method !== 'credit') {
            payments.push({ method: method, amount: Math.min(tendered, due), bank_account_id: bankAccountId });
        }

        if (tendered < due) {
            payments.push({ method: 'credit', amount: due - tendered });
        }

        if (excessAsCredit) {
            payments.push({ method: 'credit', amount: excessAsCredit });
        }

        if (payments.length === 0) {
            payments.push({ method: method, amount: due });
        }

        const payload = {
            session_id: currentSession.id,
            customer_id: selectedCustomerId,
            customer_name: selectedCustomerName === 'Walk-in' ? 'Walk-in' : selectedCustomerName,
            discount_amount: discount,
            items: cart.map(c => ({
                product_id: c.product_id,
                batch_id: c.batch_id,
                product_name: c.product_name,
                barcode: c.barcode,
                sku: c.sku,
                quantity: c.quantity,
                unit_price: c.unit_price,
                discount_amount: c.discount_amount || 0,
            })),
            payments: payments,
            tendered_amount: tendered,
            notes: notes || null,
        };

        $('#completePaymentBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: '/api/pos/transaction/process',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function (res) {
                clearCart();
                showToast('Sale completed! Receipt: ' + res.transaction.receipt_no);
                loadProducts();
                loadSession();
                $('#completePaymentBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Complete Sale');

                const printWindow = window.open('/api/pos/receipt/' + res.transaction.id + '/print', '_blank', 'width=400,height=600');
                if (printWindow) {
                    printWindow.onload = function () {
                        setTimeout(function () { printWindow.print(); }, 500);
                    };
                }
            },
            error: function (xhr) {
                $('#completePaymentBtn').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> Complete Sale');
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to complete sale', 'error');
            }
        });
    }

    // ── Hold / Resume ────────────────────────────────────
    $('#holdBtn').on('click', function () {
        if (!cart.length || !currentSession) return;
        Swal.fire({
            title: 'Hold Cart',
            input: 'text', inputLabel: 'Note (optional)',
            showCancelButton: true, confirmButtonText: 'Hold',
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/api/pos/hold/store', {
                    session_id: currentSession.id,
                    cart_data: JSON.stringify({ items: cart, customer_id: selectedCustomerId, customer_name: selectedCustomerName, discount: getCartDiscount() }),
                    note: result.value || '',
                }).done(function () {
                    clearCart();
                    showToast('Cart held.');
                    loadHolds();
                }).fail(function (xhr) { Swal.fire('Error', xhr.responseJSON?.message, 'error'); });
            }
        });
    });

    function loadHolds() {
        if (!currentSession) return;
        $.get('/api/pos/session/' + currentSession.id + '/holds', function (res) {
            $('#resumeBtn').toggle(res.holds && res.holds.length > 0);
        }).fail(() => {});
    }

    $('#resumeBtn').on('click', function () {
        if (!currentSession) return;
        $.get('/api/pos/session/' + currentSession.id + '/holds', function (res) {
            let html = '';
            if (!res.holds || !res.holds.length) { html = '<p class="text-muted">No suspended carts.</p>'; }
            else {
                res.holds.forEach(h => {
                    const data = h.cart_data || {};
                    const itemCount = data.items ? data.items.length : 0;
                    html += '<div class="d-flex justify-content-between align-items-center p-2 border-bottom">';
                    html += '<div><small class="text-muted">' + (h.note || 'No note') + '</small><br><small>' + itemCount + ' items</small></div>';
                    html += '<div><button class="btn btn-sm btn-outline-primary resume-hold-btn me-1" data-id="' + h.id + '"><i class="bi bi-play"></i></button><button class="btn btn-sm btn-outline-danger delete-hold-btn" data-id="' + h.id + '"><i class="bi bi-x"></i></button></div>';
                    html += '</div>';
                });
            }
            $('#holdList').html(html);
            $('#holdModal').modal('show');
        });
    });

    $(document).on('click', '.resume-hold-btn', function () {
        const id = $(this).data('id');
        $.get('/api/pos/hold/' + id + '/resume', function (res) {
            const data = res.hold.cart_data || { items: [], customer_id: null, customer_name: 'Walk-in', discount: 0 };
            cart = (data.items || []).map(c => ({ ...c }));
            selectedCustomerId = data.customer_id || null;
            selectedCustomerName = data.customer_name || 'Walk-in';
            $('#customerName').text(selectedCustomerName);
            if (data.discount) $('#discountInput').val(data.discount);
            renderCart();
            $('#holdModal').modal('hide');
            $.ajax({ url: '/api/pos/hold/' + id, method: 'DELETE' });
            loadHolds();
        });
    });

    $(document).on('click', '.delete-hold-btn', function () {
        const id = $(this).data('id');
        $.ajax({ url: '/api/pos/hold/' + id, method: 'DELETE' }).done(function () { loadHolds(); });
    });

    // ── Toast ─────────────────────────────────────────────
    function showToast(msg) {
        $('#toastMessage').text(msg);
        const toast = new bootstrap.Toast(document.getElementById('successToast'));
        toast.show();
    }

    // ── Shortcuts Modal ──────────────────────────────────
    $(document).on('click', '#shortcutHelp', function (e) {
        e.preventDefault();
        $('#shortcutsModal').modal('show');
    });

    // ── Keyboard Shortcuts ───────────────────────────────
    $(document).on('keydown', function (e) {
        if (e.key === 'F2') {
            e.preventDefault();
            $('#customerBadge').click();
        }
        if (e.key === 'F3') {
            e.preventDefault();
            $('#barcodeInput').focus().select();
        }
        if (e.key === 'F4') {
            e.preventDefault();
            const last = $('.cart-item').last();
            if (last.length) last.find('.qty-input').focus().select();
        }
        if (e.key === 'F6') {
            e.preventDefault();
            $('#discountInput').focus().select();
        }
        if (e.key === 'F7') {
            e.preventDefault();
            $('.payment-type-btn').filter(function () { return $(this).find('input').val() === 'full'; }).click();
        }
        if (e.key === 'F8') {
            e.preventDefault();
            $('.payment-type-btn').filter(function () { return $(this).find('input').val() === 'custom'; }).click();
        }
        if (e.key === 'F9') {
            e.preventDefault();
            $('.payment-method-btn').first().click();
        }
        if (e.key === 'F10') {
            e.preventDefault();
            $('.payment-method-btn').last().click();
        }
        if (e.key === 'F12' || (e.ctrlKey && e.key === 'Enter')) {
            e.preventDefault();
            if (cart.length) $('#completePaymentBtn').click();
        }
    });

    // ── Init ──────────────────────────────────────────────
    loadCategories();
    loadSession();
});
</script>
@endpush
