@extends('layouts.app')

@section('title', isset($purchase) ? 'Edit Purchase Order' : 'Create Purchase Order')
@section('page-title', isset($purchase) ? 'Edit Purchase Order' : 'Create Purchase Order')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}" class="text-decoration-none text-muted">Purchases</a></li>
    <li class="breadcrumb-item active">{{ isset($purchase) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

<form method="POST" action="{{ isset($purchase) ? route('purchase.update', $purchase) : route('purchase.store') }}" id="purchaseForm">
    @csrf
    @if(isset($purchase)) @method('PUT') @endif

    <div class="form-layout" style="grid-template-columns:1fr;">

        {{-- MAIN COLUMN --}}
        <div class="form-layout__main">

            {{-- Basic Information --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-receipt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Order Information</h6>
                        <p class="card-subtitle">Basic purchase order details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label fw-600">Reference / Invoice #</label>
                            <input type="text" name="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference', $purchase->ref_no ?? '') }}"
                                   placeholder="e.g. INV-2024-001">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplierSelect"
                                    class="form-select @error('supplier_id') is-invalid @enderror"
                                    onchange="loadProducts(this.value)">
                                <option value="" selected disabled>Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                        {{ old('supplier_id', $purchase->supplier_id ?? '') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->first_name . ' ' . $supplier->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date"
                                   class="form-control @error('order_date') is-invalid @enderror"
                                   value="{{ old('order_date', $purchase->order_date ?? date('Y-m-d')) }}">
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-3">
                            <label class="form-label fw-600">Notes</label>
                            <textarea name="notes" class="form-control" rows="1"
                                      placeholder="Any additional notes or instructions...">{{ old('notes', $purchase->notes ?? '') }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="card mb-2">
                <div class="card-header justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <i class="bi bi-box-seam text-primary-custom"></i>
                        <div>
                            <h6 class="card-title">Order Items</h6>
                            <p class="card-subtitle">Products being purchased</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary border-0" title="Keyboard shortcuts"
                            onclick="$('#shortcutsModal').modal('show')">
                        <i class="bi bi-question-lg"></i>
                    </button>
                </div>
                <div class="card-body">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="input-group" style="max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" id="barcodeInput" class="form-control"
                                   placeholder="Scan or type barcode / SKU"
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="document.getElementById('barcodeInput').value='';document.getElementById('barcodeInput').focus();">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <span class="text-muted small">or</span>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addItemRow()">
                            + Add Item
                        </button>
                    </div>

                    <div class="card_body p-0">
                        <table class="data-table w-100" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Cost (PKR)</th>
                                    <th>Batch No.</th>
                                    <th>Expiry Date</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">

                                @php
                                    $existingItems = old('items', isset($purchase) ? $purchase->items->map(fn($i) => [
                                        'product_id'   => $i->product_id,
                                        'quantity'     => $i->quantity,
                                        'unit_cost'    => $i->unit_cost,
                                        'batch_number' => $i->batch_number ?? '',
                                        'expiry_date'  => $i->expiry_date ?? '',
                                    ])->toArray() : []);
                                @endphp

                                @forelse($existingItems as $idx => $item)
                                <tr class="item-row" data-index="{{ $idx }}">
                                    <td>
                                        <select name="items[{{ $idx }}][product_id]"
                                                class="form-control form-control--sm product-select"
                                                onchange="fillUnitCost({{ $idx }}, this); toggleExpiryFields({{ $idx }}, this)" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                    data-cost="{{ $product->purchase_price }}"
                                                    data-is-expiry="{{ $product->is_expiry }}"
                                                    {{ ($item['product_id'] ?? '') == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $idx }}][quantity]"
                                               class="form-control form-control--sm item-qty"
                                               value="{{ $item['quantity'] ?? 1 }}" min="1" required
                                               oninput="recalcRow({{ $idx }})">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $idx }}][unit_cost]"
                                               class="form-control form-control--sm item-cost"
                                               value="{{ $item['unit_cost'] ?? '' }}" min="0" step="0.01"
                                               placeholder="0.00" required
                                               oninput="recalcRow({{ $idx }})">
                                    </td>
                                    <td class="batch-cell">
                                        <input type="text" name="items[{{ $idx }}][batch_number]"
                                               class="form-control form-control--sm"
                                               value="{{ $item['batch_number'] ?? '' }}"
                                               placeholder="Batch #">
                                    </td>
                                    <td class="expiry-cell">
                                        <input type="date" name="items[{{ $idx }}][expiry_date]"
                                               class="form-control form-control--sm"
                                               value="{{ $item['expiry_date'] ?? '' }}">
                                    </td>
                                    <td>
                                        <span class="item-subtotal fw-600">0.00</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeItemRow(this)">✕</button>
                                    </td>
                                </tr>
                                @empty
                                <tr id="noItemsRow">
                                    <td colspan="7" class="empty-state">
                                        <div class="empty-state__inner empty-state__inner--sm">
                                            <span>No items added yet.</span>
                                            <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="addItemRow()">Add one</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- Payment & Shipping --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-credit-card text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Payment &amp; Details</h6>
                        <p class="card-subtitle">Payment method and discount</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        {{-- Payment Method --}}
                        <div class="col-md-2">
                            <label class="form-label fw-600">Payment Method</label>
                            <select name="payment_method" id="paymentMethodSelect"
                                    class="form-select @error('payment_method') is-invalid @enderror"
                                    onchange="handlePaymentMethodChange(this.value)">
                                <option value="" disabled selected>Select Method</option>
                                <option value="cash"      {{ old('payment_method', $purchase->payment_method ?? '') == 'cash'      ? 'selected' : '' }}>Cash</option>
                                <option value="bank"      {{ old('payment_method', $purchase->payment_method ?? '') == 'bank'      ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="col-md-2">
                            <label class="form-label fw-600">Status</label>
                            <select name="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                <option value="pending"   {{ old('status', $purchase->status ?? '') == 'pending'   ? 'selected' : '' }}>Pending</option>
                                <option value="received"  {{ old('status', $purchase->status ?? 'received') == 'received'  ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ old('status', $purchase->status ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Bank Account (shown only when Bank Transfer is selected) --}}
                        <div class="col-md-2" id="bankAccountWrapper" style="display: none;">
                            <label class="form-label fw-600">Bank Account</label>
                            <select name="bank_account_id" id="bankAccountSelect"
                                    class="form-select @error('bank_account_id') is-invalid @enderror">
                                <option value="" disabled selected>Loading accounts...</option>
                            </select>
                            @error('bank_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Discount Type --}}
                        <div class="col-md-2">
                            <label class="form-label fw-600">Discount Type</label>
                            <div class="d-flex gap-3 mt-2">
                                <label class="form-check">
                                    <input type="radio" name="discount_type" value="percent" id="discountTypePercent"
                                           class="form-check-input"
                                           {{ old('discount_type', $purchase->discount_type ?? 'percent') == 'percent' ? 'checked' : '' }}
                                           onchange="updateTotals()">
                                    <span class="form-check-label">Percent (%)</span>
                                </label>
                                <label class="form-check">
                                    <input type="radio" name="discount_type" value="fixed" id="discountTypeFixed"
                                           class="form-check-input"
                                           {{ old('discount_type', $purchase->discount_type ?? '') == 'fixed' ? 'checked' : '' }}
                                           onchange="updateTotals()">
                                    <span class="form-check-label">Fixed</span>
                                </label>
                            </div>
                            @error('discount_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Discount --}}
                        <div class="col-md-2">
                            <label class="form-label fw-600">Discount</label>
                            <input type="number" id="discountInput" name="discount"
                                   class="form-control @error('discount') is-invalid @enderror"
                                   value="{{ old('discount', $purchase->discount ?? 0) }}" min="0" step="0.01"
                                   placeholder="0.00"
                                   oninput="updateTotals()">
                            @error('discount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Paid Amount --}}
                        <div class="col-md-2">
                            <label class="form-label fw-600">Paid Amount</label>
                            <input type="number" id="paidAmountInput" name="paid_amount"
                                   class="form-control @error('paid_amount') is-invalid @enderror"
                                   value="{{ old('paid_amount', $purchase->paid_amount ?? 0) }}" min="0" step="0.01"
                                   placeholder="0.00"
                                   oninput="updateTotals()">
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="card mb-2" style="max-width: 500px; margin-left: auto;">
                <div class="card-body">
                    <table class="w-100" style="font-size: 0.875rem; border-collapse: collapse;">
                        <tr>
                            <td class="py-1 text-muted">Subtotal</td>
                            <td class="py-1 text-end fw-600">PKR <span id="summarySubtotal">0.00</span></td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">Discount</td>
                            <td class="py-1 text-end fw-600 text-danger">− PKR <span id="summaryDiscount">0.00</span></td>
                        </tr>
                        <tr style="border-top: 1px solid #eee;">
                            <td class="py-1 fw-600" style="font-size: 0.9rem;">Grand Total</td>
                            <td class="py-1 text-end fw-600 text-primary-custom" style="font-size: 1rem;">
                                PKR <span id="summaryTotal">0.00</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">Paid Amount</td>
                            <td class="py-1 text-end fw-600 text-success">PKR <span id="summaryPaid">0.00</span></td>
                        </tr>
                        <tr style="border-top: 1px solid #eee;">
                            <td class="py-1 fw-600">Due / Balance</td>
                            <td class="py-1 text-end fw-600" id="summaryDueWrapper" style="font-size: 1rem;">
                                PKR <span id="summaryDue">0.00</span>
                            </td>
                        </tr>
                    </table>

                    {{-- Hidden totals for form submission --}}
                    <input type="hidden" name="subtotal"       id="hiddenSubtotal">
                    <input type="hidden" name="total_discount" id="hiddenDiscount">
                    <input type="hidden" name="grand_total"    id="hiddenTotal">
                    <input type="hidden" name="due_amount"     id="hiddenDue">
                </div>
            </div>

        </div>

        {{-- Buttons --}}
        <div class="d-flex gap-2 justify-content-end mt-3">
            <a href="{{ route('purchase.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>
                {{ isset($purchase) ? 'Update Order' : 'Save Order' }}
            </button>
        </div>

        </div>{{-- /form-layout__main --}}
    </div>{{-- /form-layout --}}
</form>

{{-- Shortcuts Modal --}}
<div class="modal fade" id="shortcutsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-keyboard me-1"></i> Keyboard Shortcuts</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr><td><kbd>F2</kbd></td><td>Add Item Row</td></tr>
                        <tr><td><kbd>F8</kbd></td><td>Remove Last Item</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = {{ count($existingItems ?? []) }};

// ── Payment Method → Bank Accounts ───────────────────────────────────────────
function handlePaymentMethodChange(value) {
    const wrapper = document.getElementById('bankAccountWrapper');
    if (value === 'bank') {
        wrapper.style.display = '';
        loadBankAccounts();
    } else {
        wrapper.style.display = 'none';
        document.getElementById('bankAccountSelect').innerHTML = '<option value="" disabled selected>Loading accounts...</option>';
    }
}

function loadBankAccounts() {
    const select = document.getElementById('bankAccountSelect');
    select.innerHTML = '<option value="" disabled selected>Loading...</option>';

    $.ajax({
        url: '{{ route("bank-accounts.index") }}', // adjust route name as needed
        type: 'GET',
        success: function (accounts) {
            select.innerHTML = '<option value="" disabled selected>Select Bank Account</option>';
            if (!accounts || accounts.length === 0) {
                select.innerHTML = '<option value="" disabled selected>No accounts found</option>';
                return;
            }
            accounts.forEach(function (account) {
                const label = account.bank_name + ' — ' + account.account_number + (account.account_title ? ' (' + account.account_title + ')' : '');
                select.innerHTML += `<option value="${account.id}">${label}</option>`;
            });

            // Pre-select if editing
            @if(isset($purchase) && $purchase->bank_account_id)
            select.value = '{{ $purchase->bank_account_id }}';
            @endif
        },
        error: function () {
            select.innerHTML = '<option value="" disabled selected>Failed to load accounts</option>';
        }
    });
}

// On page load: if payment method is already "bank" (edit mode), show bank accounts
document.addEventListener('DOMContentLoaded', function () {
    const method = document.getElementById('paymentMethodSelect').value;
    if (method === 'bank') {
        handlePaymentMethodChange('bank');
    }

    // Load products if supplier is pre-selected (edit mode)
    const supplierSelect = document.getElementById('supplierSelect');
    if (supplierSelect.value) {
        loadProducts(supplierSelect.value);
    }

    // Recalc all existing rows on load
    document.querySelectorAll('.item-row').forEach(row => {
        recalcRow(row.dataset.index);
    });

    // Toggle expiry fields for existing rows (products already loaded via loadProducts callback)
    document.querySelectorAll('.item-row').forEach(row => {
        const sel = row.querySelector('.product-select');
        const idx = row.dataset.index;
        if (sel && sel.value && idx !== undefined) {
            toggleExpiryFields(parseInt(idx), sel);
        }
    });
});

const supplierProductsUrlTemplate = '{{ route("supplier.products", ["id" => ":id"]) }}';
let productsCache = [];

// ── Populate a single select from cache ──────────────────────────────────────
function populateSelectFromCache(select) {
    if (!productsCache.length) return;
    const currentVal = select.value;
    productsCache.forEach(p => {
        const selected = p.id == currentVal ? 'selected' : '';
        select.innerHTML += `<option value="${p.id}" data-cost="${p.purchase_price}" data-is-expiry="${p.is_expiry}" ${selected}>${p.name}</option>`;
    });
}

// ── Load Products by Supplier ────────────────────────────────────────────────
function loadProducts(id) {
    if (!id) return;
    $.ajax({
        url: supplierProductsUrlTemplate.replace(':id', id),
        type: 'GET',
        success: function (products) {
            productsCache = products;
            document.querySelectorAll('.product-select').forEach(select => {
                const currentVal = select.value;
                select.innerHTML = '<option value="">Select Product</option>';
                products.forEach(p => {
                    const selected = p.id == currentVal ? 'selected' : '';
                    select.innerHTML += `<option value="${p.id}" data-cost="${p.purchase_price}" data-is-expiry="${p.is_expiry}" ${selected}>${p.name}</option>`;
                });
                // Re-init Select2 so new options are reflected in the UI
                if (window.initSelect2) initSelect2($(select));
            });
            // Toggle expiry fields for rows that already have a product selected
            document.querySelectorAll('.item-row').forEach(row => {
                const sel = row.querySelector('.product-select');
                const idx = row.dataset.index;
                if (sel && sel.value && idx !== undefined) {
                    toggleExpiryFields(parseInt(idx), sel);
                }
            });
        }
    });
}

// ── Add Item Row ─────────────────────────────────────────────────────────────
function addItemRow() {
    document.getElementById('noItemsRow')?.remove();
    const i = itemIndex++;
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.dataset.index = i;
    row.innerHTML = `
        <td>
            <select name="items[${i}][product_id]" class="form-control form-control--sm product-select"
                    onchange="fillUnitCost(${i}, this); toggleExpiryFields(${i}, this)" required>
                <option value="">Select Product</option>
            </select>
        </td>
        <td style="max-width: 100px;">
            <input type="number" name="items[${i}][quantity]"
                   class="form-control form-control--sm item-qty"
                   value="1" min="1" required oninput="recalcRow(${i})">
        </td>
        <td style="max-width: 130px;">
            <input type="number" name="items[${i}][unit_cost]"
                   class="form-control form-control--sm item-cost"
                   min="0" step="0.01" placeholder="0.00" required oninput="recalcRow(${i})">
        </td>
        <td style="max-width: 130px;" class="batch-cell">
            <input type="text" name="items[${i}][batch_number]"
                   class="form-control form-control--sm"
                   placeholder="Batch #">
        </td>
        <td class="expiry-cell">
            <input type="date" name="items[${i}][expiry_date]"
                   class="form-control form-control--sm">
        </td>
        <td><span class="item-subtotal fw-600">0.00</span></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeItemRow(this)">✕</button>
        </td>
    `;
    // Populate options from cache to avoid an AJAX call that would close Select2
    const select = row.querySelector('.product-select');
    if (select) populateSelectFromCache(select);
    document.getElementById('itemRows').appendChild(row);
}

// ── Remove Item Row ──────────────────────────────────────────────────────────
function removeItemRow(btn) {
    btn.closest('tr').remove();
    updateTotals();
    if (!document.querySelectorAll('.item-row').length) {
        document.getElementById('itemRows').innerHTML = `
            <tr id="noItemsRow"><td colspan="7" class="empty-state">
                <div class="empty-state__inner empty-state__inner--sm">
                    <span>No items added yet.</span>
                    <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="addItemRow()">Add one</button>
                </div>
            </td></tr>`;
    }
}

// ── Auto-fill unit cost from selected product ────────────────────────────────
function fillUnitCost(idx, select) {
    const row = document.querySelector(`.item-row[data-index="${idx}"]`);
    if (!row) return;
    const cost = select.options[select.selectedIndex]?.dataset.cost || '';
    row.querySelector('.item-cost').value = cost;
    recalcRow(idx);
}

// ── Toggle batch/expiry fields based on product expiry tracking ─────────────
function toggleExpiryFields(idx, select) {
    const row = document.querySelector(`.item-row[data-index="${idx}"]`);
    if (!row) return;
    const opt = select.options[select.selectedIndex];
    // Disable fields when product does NOT track expiry (always visible)
    const isExpiry = opt && opt.value && opt.dataset.isExpiry ? parseInt(opt.dataset.isExpiry) : 1;
    const batchInput = row.querySelector('.batch-cell input');
    const expiryInput = row.querySelector('.expiry-cell input');
    if (batchInput) batchInput.disabled = !isExpiry;
    if (expiryInput) expiryInput.disabled = !isExpiry;
}

// ── Alert when user tries to edit disabled batch/expiry fields ────────────
$(document).on('focus', '.batch-cell input:disabled, .expiry-cell input:disabled', function () {
    this.blur();
    Swal.fire({
        icon: 'info',
        title: 'Expiry Tracking Disabled',
        text: 'Please enable "Track Expiry" for this product in the product settings to add batch number and expiry date.',
        timer: 5000,
        showConfirmButton: true,
    });
});

// ── Recalculate single row subtotal ─────────────────────────────────────────
function recalcRow(idx) {
    const row = document.querySelector(`.item-row[data-index="${idx}"]`);
    if (!row) return;
    const qty  = parseFloat(row.querySelector('.item-qty')?.value)  || 0;
    const cost = parseFloat(row.querySelector('.item-cost')?.value) || 0;
    const subtotal = qty * cost;
    row.querySelector('.item-subtotal').textContent = subtotal.toFixed(2);
    updateTotals();
}

// ── Update order-level totals ────────────────────────────────────────────────
function updateTotals() {
    // 1. Sum all row subtotals
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty  = parseFloat(row.querySelector('.item-qty')?.value)  || 0;
        const cost = parseFloat(row.querySelector('.item-cost')?.value) || 0;
        subtotal += qty * cost;
    });

    // 2. Resolve discount
    const discountValue = parseFloat(document.getElementById('discountInput')?.value) || 0;
    const discountType  = document.querySelector('input[name="discount_type"]:checked')?.value || 'percent';
    let   discountAmt   = 0;
    if (discountType === 'percent') {
        discountAmt = subtotal * (discountValue / 100);
    } else {
        discountAmt = Math.min(discountValue, subtotal); // fixed can't exceed subtotal
    }

    // 3. Grand total
    const grandTotal = subtotal - discountAmt;

    // 4. Paid & due
    const paidAmount = parseFloat(document.getElementById('paidAmountInput')?.value) || 0;
    const due        = grandTotal - paidAmount;

    // 5. Update display
    document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
    document.getElementById('summaryDiscount').textContent = discountAmt.toFixed(2);
    document.getElementById('summaryTotal').textContent    = grandTotal.toFixed(2);
    document.getElementById('summaryPaid').textContent     = paidAmount.toFixed(2);
    document.getElementById('summaryDue').textContent      = Math.abs(due).toFixed(2);

    // Color the due row — red if still owed, green if overpaid, muted if settled
    const dueWrapper = document.getElementById('summaryDueWrapper');
    if (due > 0.009) {
        dueWrapper.style.color = 'var(--bs-danger, #dc3545)';
        document.querySelector('.item-row') && (document.getElementById('summaryDue').textContent = due.toFixed(2));
    } else if (due < -0.009) {
        dueWrapper.style.color = 'var(--bs-success, #198754)';
        document.getElementById('summaryDue').textContent = Math.abs(due).toFixed(2) + ' (overpaid)';
    } else {
        dueWrapper.style.color = 'var(--bs-success, #198754)';
        document.getElementById('summaryDue').textContent = '0.00 (settled)';
    }

    // 6. Populate hidden fields for form submission
    document.getElementById('hiddenSubtotal').value = subtotal.toFixed(2);
    document.getElementById('hiddenDiscount').value = discountAmt.toFixed(2);
    document.getElementById('hiddenTotal').value    = grandTotal.toFixed(2);
    document.getElementById('hiddenDue').value      = due.toFixed(2);
}

// ── Keyboard Shortcuts ─────────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    const tag = document.activeElement?.tagName?.toLowerCase();
    const isInput = tag === 'input' || tag === 'textarea' || tag === 'select';
    const isContentEditable = document.activeElement?.isContentEditable;

    // F2 → Add Item Row (only when not typing in a field)
    if (e.key === 'F2' && !isInput && !isContentEditable) {
        e.preventDefault();
        addItemRow();
        const rows = document.querySelectorAll('.item-row');
        if (rows.length) {
            const lastSelect = rows[rows.length - 1].querySelector('.product-select');
            if (lastSelect) {
                lastSelect.focus();
                setTimeout(function () {
                    try { $(lastSelect).select2('open'); } catch (_) {}
                }, 100);
            }
        }
    }

    // F8 → Remove last item row (only when not typing in a field)
    if (e.key === 'F8' && !isInput && !isContentEditable) {
        e.preventDefault();
        const rows = document.querySelectorAll('.item-row');
        if (rows.length) removeItemRow(rows[rows.length - 1].querySelector('button'));
    }
});

// ── Barcode Scanning ────────────────────────────────────────────────────────
const barcodeLookupUrl = '{{ route("purchase.product.lookup", ["barcode" => ":barcode"]) }}';
const barcodeInput = document.getElementById('barcodeInput');

barcodeInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const q = this.value.trim();
        if (!q) return;

        const url = barcodeLookupUrl.replace(':barcode', encodeURIComponent(q));
        $.get(url, function (res) {
            if (res.found && res.product) {
                const p = res.product;
                document.getElementById('noItemsRow')?.remove();
                const i = itemIndex++;
                const row = document.createElement('tr');
                row.className = 'item-row';
                row.dataset.index = i;
                row.innerHTML = `
                    <td>
                        <select name="items[${i}][product_id]" class="form-control form-control--sm product-select"
                                onchange="fillUnitCost(${i}, this); toggleExpiryFields(${i}, this)" required>
                            <option value="">Select Product</option>
                            <option value="${p.id}" data-cost="${p.purchase_price || 0}" data-is-expiry="${p.is_expiry || 0}" selected>${p.name}</option>
                        </select>
                    </td>
                    <td style="max-width: 100px;">
                        <input type="number" name="items[${i}][quantity]"
                               class="form-control form-control--sm item-qty"
                               value="1" min="1" required oninput="recalcRow(${i})">
                    </td>
                    <td style="max-width: 130px;">
                        <input type="number" name="items[${i}][unit_cost]"
                               class="form-control form-control--sm item-cost"
                               value="${p.purchase_price || ''}" min="0" step="0.01" placeholder="0.00" required oninput="recalcRow(${i})">
                    </td>
        <td style="max-width: 130px;" class="batch-cell">
            <input type="text" name="items[${i}][batch_number]"
                   class="form-control form-control--sm" placeholder="Batch #">
        </td>
        <td class="expiry-cell">
            <input type="date" name="items[${i}][expiry_date]"
                   class="form-control form-control--sm">
        </td>
                    <td><span class="item-subtotal fw-600">0.00</span></td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeItemRow(this)">✕</button>
                    </td>
                `;
                document.getElementById('itemRows').appendChild(row);
                const newSelect = row.querySelector('.product-select');
                if (newSelect) {
                    $(newSelect).trigger('change');
                    fillUnitCost(i, newSelect);
                }
                barcodeInput.value = '';
                barcodeInput.focus();
            } else {
                Swal.fire({ icon: 'warning', title: 'Not Found', text: res.message || 'No product matches that barcode/SKU.', timer: 2000, showConfirmButton: false });
                barcodeInput.select();
            }
        }).fail(function () {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Could not look up barcode. Please try again.', timer: 2000, showConfirmButton: false });
        });
    }
});
</script>
@endpush

@endsection