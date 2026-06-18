@extends('layouts.app')

@section('title', 'New Sale')
@section('page-title', 'Create Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sale.index') }}" class="text-decoration-none text-muted">Sales</a></li>
    <li class="breadcrumb-item active">New Sale</li>
@endsection

@section('content')

<form method="POST" action="{{ route('sale.store') }}" id="saleForm">
    @csrf

    <div class="form-layout">

        {{-- LEFT COLUMN --}}
        <div class="form-layout__main">

            {{-- Basic Information --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-receipt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Sale Information</h6>
                        <p class="card-subtitle">Basic sale details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label fw-600">Invoice # <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" id="invoiceNo"
                                   class="form-control @error('invoice_no') is-invalid @enderror"
                                   value="{{ $invoiceNo }}" readonly>
                            @error('invoice_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect"
                                    class="form-select @error('customer_id') is-invalid @enderror"
                                    onchange="loadCustomerDetails(this.value)">
                                <option value="" selected disabled>Select Customer</option>
                                @foreach($customers as $customer)
                                    @continue(str_contains(strtolower($customer->first_name . ' ' . $customer->last_name), 'walk-in'))
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->first_name . ' ' . $customer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date"
                                   class="form-control @error('sale_date') is-invalid @enderror"
                                   value="{{ date('Y-m-d') }}">
                            @error('sale_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft">Draft</option>
                                <option value="completed" selected>Completed</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Customer Details --}}
            <div class="card mb-2" id="customerDetailsCard" style="display: none;">
                <div class="card-header">
                    <i class="bi bi-person text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Customer Information</h6>
                        <p class="card-subtitle">Selected customer details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small">Email</label>
                            <p class="fw-500" id="customerEmail">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Phone</label>
                            <p class="fw-500" id="customerPhone">-</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Outstanding Balance</label>
                            <p class="fw-500 text-danger" id="customerBalance">Rs. 0.00</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sale Items --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-box-seam text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Sale Items</h6>
                        <p class="card-subtitle">Products being sold</p>
                    </div>
                </div>
                <div class="card-body">

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="input-group" style="max-width: 320px;">
                            <span class="input-group-text bg-white"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" id="barcodeInput" class="form-control"
                                   placeholder="Scan or type barcode / SKU"
                                   autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" id="barcodeClearBtn"
                                    onclick="document.getElementById('barcodeInput').value='';document.getElementById('barcodeInput').focus();">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <span class="text-muted small">or</span>
                        <button type="button" class="btn btn-primary btn-sm" onclick="addItemRow()">
                            + Add Item
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table w-100" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th width="100">Quantity</th>
                                    <th width="130">Unit Price (PKR)</th>
                                    <th width="100">Discount %</th>
                                    <th width="130">Subtotal</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                            </tbody>
                        </table>
                    </div>

                    @error('items')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Payment & Details --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-credit-card text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Payment &amp; Details</h6>
                        <p class="card-subtitle">Payment method and details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label fw-600">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type" id="paymentType"
                                    class="form-select @error('payment_type') is-invalid @enderror"
                                    onchange="toggleBankAccountField()">
                                <option value="cash" {{ old('payment_type', 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_type') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            </select>
                            @error('payment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3" id="bankAccountGroup" style="display: none;">
                            <label class="form-label fw-600">Bank Account <span class="text-danger">*</span></label>
                            <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
                                <option value="" selected disabled>Select Bank Account</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>{{ $account->bank_name }} - {{ $account->account_title }} ({{ $account->account_number }})</option>
                                @endforeach
                            </select>
                            @error('bank_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paidAmount"
                                   class="form-control @error('paid_amount') is-invalid @enderror"
                                   placeholder="0.00" min="0" step="0.01" value="{{ old('paid_amount', 0) }}"
                                   oninput="calculateBalance()">
                            @error('paid_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Notes</label>
                            <textarea name="notes" class="form-control" rows="1"
                                      placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Sale Summary --}}
            <div class="card mb-2" style="max-width: 500px; margin-left: auto;">
                <div class="card-body">
                    <table class="w-100" style="font-size: 0.875rem; border-collapse: collapse;">
                        <tr>
                            <td class="py-1 text-muted">Subtotal</td>
                            <td class="py-1 text-end fw-600">PKR <span id="subtotal">0.00</span></td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">Discount</td>
                            <td class="py-1 text-end fw-600 text-danger">− PKR <span id="totalDiscount">0.00</span></td>
                        </tr>
                        <tr style="border-top: 1px solid #eee;">
                            <td class="py-1 fw-600" style="font-size: 0.9rem;">Total Amount</td>
                            <td class="py-1 text-end fw-600 text-primary-custom" style="font-size: 1rem;">
                                PKR <span id="totalAmount">0.00</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="py-1 text-muted">Paid Amount</td>
                            <td class="py-1 text-end fw-600 text-success">PKR <span id="summaryPaid">0.00</span></td>
                        </tr>
                        <tr style="border-top: 1px solid #eee;">
                            <td class="py-1 fw-600">Due / Balance</td>
                            <td class="py-1 text-end fw-600" id="balanceWrapper" style="font-size: 1rem;">
                                PKR <span id="balanceAmount">0.00</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="form-layout__sidebar">

            {{-- Shortcuts Hint --}}
            <div class="card p-3 mt-2">
                <div class="card__header"><h2 class="card__title" style="font-size:13px;">Shortcuts</h2></div>
                <div class="card__body">
                    <div style="font-size:12px; line-height:2;">
                        <kbd>F2</kbd> Add Item &middot; <kbd>F8</kbd> Remove Last
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="d-flex gap-2 justify-content-end mt-3">
                <a href="{{ route('sale.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Create Sale
                </button>
            </div>

        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
const products = @json($products);
let itemIndex = 0;

// Batch route template
const batchUrlTemplate = '{{ route("sale.product.batches", ["id" => ":id"]) }}';

function addItemRow(product = null, qty = 1, price = 0, discount = 0, batchId = null) {
    const html = `
        <tr class="item-row" data-index="${itemIndex}">
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control form-control--sm product-select"
                        onchange="onProductChange(${itemIndex}, this)" required>
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}" data-price="${p.sale_price || 0}" data-is-expiry="${p.is_expiry || 0}" ${product && product.id == p.id ? 'selected' : ''}>${p.name}</option>`).join('')}
                </select>
            </td>
            <td style="min-width:180px;">
                <select name="items[${itemIndex}][batch_id]" class="form-control form-control--sm batch-select" id="batchSelect${itemIndex}">
                    <option value=""> Select Batch </option>
                </select>
                <small class="batch-stock-info text-muted" id="batchInfo${itemIndex}"></small>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control--sm item-qty"
                       value="${qty}" min="1" required oninput="recalcRow(${itemIndex}); clampBatchQty(${itemIndex})">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control--sm item-price"
                       value="${price}" min="0" step="0.01" placeholder="0.00" required
                       oninput="recalcRow(${itemIndex})">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][discount_percent]" class="form-control form-control--sm item-discount"
                       value="${discount}" min="0" max="100" placeholder="0" oninput="recalcRow(${itemIndex})">
            </td>
            <td>
                <div class="fw-600 item-total" id="lineTotal${itemIndex}">Rs. 0.00</div>
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItemRow(${itemIndex})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#itemRows').append(html);

    // If editing with an existing product, load batches or disable batch select
    if (product && product.id) {
        const pData = products.find(p => p.id == product.id);
        const isExpiry = pData ? (pData.is_expiry ? 1 : 0) : 1;
        updateBatchSelectState(itemIndex, product.id, isExpiry, batchId);
    }

    itemIndex++;
}

function removeItemRow(index) {
    $(`[data-index="${index}"]`).remove();
    calculateTotals();
}

function onProductChange(rowIndex, selectElement) {
    const productId = selectElement.value;
    if (!productId) return;

    // Auto-fill price
    const option = selectElement.options[selectElement.selectedIndex];
    const price = option.dataset.price || 0;
    $(`[data-index="${rowIndex}"]`).find('.item-price').val(parseFloat(price).toFixed(2));

    // Check expiry tracking
    const isExpiry = parseInt(option.dataset.isExpiry || 0);
    updateBatchSelectState(rowIndex, productId, isExpiry, null);
    recalcRow(rowIndex);
}

function loadBatchesForRow(rowIndex, productId, selectedBatchId = null) {
    const batchSelect = $(`#batchSelect${rowIndex}`);
    const batchInfo   = $(`#batchInfo${rowIndex}`);

    batchSelect.html('<option value="">Loading...</option>');
    batchInfo.text('');

    const url = batchUrlTemplate.replace(':id', productId);

    $.get(url, function (batches) {
        batchSelect.html('<option value="">— No Batch —</option>');

        if (!batches || batches.length === 0) {
            batchInfo.text('No batches with stock available.');
            return;
        }

        batches.forEach(function (b) {
            const label  = b.batch_number;
            const selected = (selectedBatchId && b.id == selectedBatchId) ? 'selected' : '';
            batchSelect.append(`<option value="${b.id}" data-qty="${b.quantity}" ${selected}>${label}</option>`);
        });

        // Auto-select first batch (FIFO) if no explicit selection
        if (!selectedBatchId && batches.length > 0) {
            batchSelect.val(batches[0].id);
        }

        updateBatchInfo(rowIndex);
    }).fail(function () {
        batchSelect.html('<option value="">— No Batch —</option>');
        batchInfo.text('Could not load batches.');
    });
}

// ── Update batch select state based on expiry tracking ────────────────────
function updateBatchSelectState(rowIndex, productId, isExpiry, selectedBatchId = null) {
    const batchSelect = $(`#batchSelect${rowIndex}`);
    const batchInfo   = $(`#batchInfo${rowIndex}`);

    if (!isExpiry) {
        batchSelect.prop('disabled', true).val('');
        const product = products.find(p => p.id == productId);
        const qty = product ? (product.quantity || 0) : 0;
        batchInfo.text(`Available Stock: ${qty}`).removeClass('text-danger').addClass('text-muted');
    } else {
        batchSelect.prop('disabled', false);
        loadBatchesForRow(rowIndex, productId, selectedBatchId);
    }
}

function clampBatchQty(rowIndex) {
    const row = $(`[data-index="${rowIndex}"]`);
    const qtyInput = row.find('.item-qty');
    const batchSelect = row.find('.batch-select');
    const qty = parseInt(qtyInput.val()) || 0;

    if (batchSelect.prop('disabled')) {
        // Product does not track expiry — validate against total stock
        const productSelect = row.find('.product-select');
        const productId = productSelect.val();
        if (!productId) return;
        const product = products.find(p => p.id == productId);
        const totalStock = product ? (product.quantity || 0) : 0;
        if (totalStock > 0 && qty > totalStock) {
            qtyInput.val(totalStock);
            recalcRow(rowIndex);
            Swal.fire({ icon: 'warning', title: 'Quantity Adjusted', text: `Only ${totalStock} available in stock.`, timer: 2000, showConfirmButton: false });
        }
    } else {
        const avail = parseInt(batchSelect.find('option:selected').data('qty') || 0);
        if (avail > 0 && qty > avail) {
            qtyInput.val(avail);
            recalcRow(rowIndex);
            Swal.fire({ icon: 'warning', title: 'Quantity Adjusted', text: `Only ${avail} available in this batch.`, timer: 2000, showConfirmButton: false });
        }
    }
}

function updateBatchInfo(rowIndex) {
    const batchSelect = $(`#batchSelect${rowIndex}`);
    const batchInfo   = $(`#batchInfo${rowIndex}`);
    const selectedOption = batchSelect.find('option:selected');
    const availableQty = parseInt(selectedOption.data('qty') || 0);

    if (availableQty > 0) {
        batchInfo.text(`Available: ${availableQty}`).removeClass('text-danger').addClass('text-muted');
    } else if (batchSelect.val()) {
        batchInfo.text('Out of stock!').removeClass('text-muted').addClass('text-danger');
    } else {
        batchInfo.text('');
    }
}

function recalcRow(index) {
    const row = $(`[data-index="${index}"]`);
    const qty = parseFloat(row.find('.item-qty').val()) || 0;
    const price = parseFloat(row.find('.item-price').val()) || 0;
    const discount = parseFloat(row.find('.item-discount').val()) || 0;

    const subtotal = qty * price;
    const discountAmount = (subtotal * discount) / 100;
    const lineTotal = subtotal - discountAmount;

    row.find('.item-total').text('Rs. ' + lineTotal.toFixed(2));
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let totalDiscount = 0;

    $('#itemRows tr').each(function () {
        const qty = parseFloat($(this).find('.item-qty').val()) || 0;
        const price = parseFloat($(this).find('.item-price').val()) || 0;
        const discount = parseFloat($(this).find('.item-discount').val()) || 0;

        const rowSubtotal = qty * price;
        const rowDiscount = (rowSubtotal * discount) / 100;

        subtotal += rowSubtotal;
        totalDiscount += rowDiscount;
    });

    const totalAmount = subtotal - totalDiscount;

    $('#subtotal').text(subtotal.toFixed(2));
    $('#totalDiscount').text(totalDiscount.toFixed(2));
    $('#totalAmount').text(totalAmount.toFixed(2));

    calculateBalance();
}

function calculateBalance() {
    const totalAmount = parseFloat($('#totalAmount').text()) || 0;
    const paidAmount = parseFloat($('#paidAmount').val()) || 0;
    const due = totalAmount - paidAmount;

    $('#summaryPaid').text(paidAmount.toFixed(2));
    $('#balanceAmount').text(Math.abs(due).toFixed(2));

    const wrapper = $('#balanceWrapper');
    if (due > 0.009) {
        wrapper.css('color', 'var(--bs-danger, #dc3545)');
    } else if (due < -0.009) {
        wrapper.css('color', 'var(--bs-success, #198754)');
        $('#balanceAmount').text(Math.abs(due).toFixed(2) + ' (overpaid)');
    } else {
        wrapper.css('color', 'var(--bs-success, #198754)');
        $('#balanceAmount').text('0.00 (settled)');
    }
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
        if (rows.length) {
            const lastRow = rows[rows.length - 1];
            const idx = lastRow.dataset.index;
            removeItemRow(idx);
        }
    }
});

// ── Barcode Scanning ────────────────────────────────────────────────────────
const barcodeLookupUrl = '{{ route("sale.product.lookup", ["barcode" => ":barcode"]) }}';
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
                addItemRow({ id: p.id, name: p.name, sale_price: p.sale_price }, 1, p.sale_price, 0, p.batch_id);
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

function loadCustomerDetails(customerId) {
    if (!customerId) {
        $('#customerDetailsCard').hide();
        return;
    }

    $.get('/sale/customer/' + customerId + '/details', function (data) {
        $('#customerEmail').text(data.email || '-');
        $('#customerPhone').text(data.phone || '-');
        $('#customerBalance').text('Rs. ' + data.balance.toFixed(2));
        $('#customerDetailsCard').show();
    });
}

function toggleBankAccountField() {
    const paymentType = document.getElementById('paymentType').value;
    const bankAccountGroup = document.getElementById('bankAccountGroup');
    bankAccountGroup.style.display = paymentType === 'bank_transfer' ? 'block' : 'none';
}

$(function () {
    // Add one empty row by default
    addItemRow();

    toggleBankAccountField();

    // Update batch info and clamp qty on batch change
    $(document).on('change', '.batch-select', function () {
        const rowIndex = $(this).closest('tr.item-row').data('index');
        updateBatchInfo(rowIndex);
        clampBatchQty(rowIndex);
    });

    // Form submission validation
    $('#saleForm').on('submit', function (e) {
        const itemCount = $('#itemRows tr').length;
        if (itemCount === 0) {
            e.preventDefault();
            alert('Please add at least one item to the sale');
            return false;
        }
    });
});
</script>
@endpush

