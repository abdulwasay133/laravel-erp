@extends('layouts.app')

@section('title', 'Edit Sale')
@section('page-title', 'Edit Sale')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('sale.index') }}" class="text-decoration-none text-muted">Sales</a></li>
    <li class="breadcrumb-item"><a href="{{ route('sale.show', $sale->id) }}" class="text-decoration-none text-muted">{{ $sale->invoice_no }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

<form method="POST" action="{{ route('sale.update', $sale->id) }}" id="saleForm">
    @csrf
    @method('PUT')

    <div class="form-layout">

        {{-- LEFT COLUMN --}}
        <div class="form-layout__main">

            {{-- Basic Information --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-receipt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Sale Information</h6>
                        <p class="card-subtitle">Edit sale details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label fw-600">Invoice # <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" id="invoiceNo"
                                   class="form-control @error('invoice_no') is-invalid @enderror"
                                   value="{{ $sale->invoice_no }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect"
                                    class="form-select @error('customer_id') is-invalid @enderror"
                                    onchange="loadCustomerDetails(this.value)">
                                <option value="" disabled>Select Customer</option>
                                @foreach($customers as $customer)
                                    @continue(str_contains(strtolower($customer->first_name . ' ' . $customer->last_name), 'walk-in'))
                                    <option value="{{ $customer->id }}" {{ $sale->customer_id == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->first_name . ' ' . $customer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Sale Date <span class="text-danger">*</span></label>
                            <input type="date" name="sale_date"
                                   class="form-control @error('sale_date') is-invalid @enderror"
                                   value="{{ $sale->sale_date }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select">
                                <option value="draft" {{ $sale->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="completed" {{ $sale->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Customer Details --}}
            <div class="card mb-2">
                <div class="card-header">
                    <i class="bi bi-person text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Customer Information</h6>
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

                    <button type="button" class="btn btn-primary btn-sm mb-3" onclick="addItemRow()">
                        + Add Item
                    </button>

                    <div class="table-responsive">
                        <table class="data-table w-100" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Batch</th>
                                    <th width="120">Quantity</th>
                                    <th width="140">Unit Price (PKR)</th>
                                    <th width="120">Discount %</th>
                                    <th width="140">Line Total</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN (SUMMARY) --}}
        <div class="form-layout__side">

            {{-- Sale Summary --}}
            <div class="card sticky-top">
                <div class="card-header">
                    <i class="bi bi-calculator text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Sale Summary</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span class="fw-600" id="subtotal">Rs. 0.00</span>
                    </div>
                    <hr>
                    <div class="summary-row">
                        <span>Total Discount:</span>
                        <span class="fw-600" id="totalDiscount">Rs. 0.00</span>
                    </div>
                    <hr>
                    <div class="summary-row">
                        <span>Total Amount:</span>
                        <span class="fw-700 fs-5" id="totalAmount">Rs. 0.00</span>
                    </div>
                </div>
            </div>

            {{-- Payment Information --}}
            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-credit-card text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Payment</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-600">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" id="paymentType" class="form-select" onchange="toggleBankAccountField()">
                            <option value="cash" {{ old('payment_type', optional($sale->payments->first())->payment_type ?? 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ old('payment_type', optional($sale->payments->first())->payment_type ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>

                    <div class="mb-3" id="bankAccountGroup" style="display: none;">
                        <label class="form-label fw-600">Bank Account <span class="text-danger">*</span></label>
                        <select name="bank_account_id" class="form-select">
                            <option value="" selected disabled>Select Bank Account</option>
                            @foreach($bankAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('bank_account_id', optional($sale->payments->first())->bank_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->bank_name }} - {{ $account->account_title }} ({{ $account->account_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Paid Amount</label>
                        <input type="number" name="paid_amount" id="paidAmount"
                               class="form-control @error('paid_amount') is-invalid @enderror"
                               placeholder="0.00" min="0" step="0.01" value="{{ old('paid_amount', $sale->paid_amount) }}"
                               oninput="calculateBalance()">
                    </div>

                    <div class="summary-row">
                        <span>Balance:</span>
                        <span class="fw-600 text-danger" id="balanceAmount">Rs. 0.00</span>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-600">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $sale->notes }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle me-1"></i> Update Sale
                    </button>
                </div>
            </div>

        </div>

    </div>

</form>

@endsection

@push('scripts')
<script>
const products = @json($products);
const existingItems = @json($sale->items);
let itemIndex = 0;
function toggleBankAccountField() {
    const paymentType = document.getElementById('paymentType').value;
    const bankAccountGroup = document.getElementById('bankAccountGroup');
    bankAccountGroup.style.display = paymentType === 'bank_transfer' ? 'block' : 'none';
}

function addItemRow(product = null, qty = 1, price = 0, discount = 0) {
    const html = `
        <tr class="item-row" data-index="${itemIndex}">
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control form-control--sm product-select"
                        onchange="loadProductPrice(${itemIndex}, this)" required>
                    <option value="">Select Product</option>
                    ${products.map(p => `<option value="${p.id}" ${product && product.id == p.id ? 'selected' : ''}>${p.name}</option>`).join('')}
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control--sm item-qty"
                       value="${qty}" min="1" required oninput="recalcRow(${itemIndex})">
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
    itemIndex++;
}

function removeItemRow(index) {
    $(`[data-index="${index}"]`).remove();
    calculateTotals();
}

function loadProductPrice(rowIndex, selectElement) {
    const productId = selectElement.value;
    if (!productId) return;

    $.get('/sale/product/' + productId + '/price', function (data) {
        const row = $(`[data-index="${rowIndex}"]`);
        row.find('.item-price').val(data.price);
        recalcRow(rowIndex);
    });
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

    $('#subtotal').text('Rs. ' + subtotal.toFixed(2));
    $('#totalDiscount').text('Rs. ' + totalDiscount.toFixed(2));
    $('#totalAmount').text('Rs. ' + totalAmount.toFixed(2));

    calculateBalance();
}

function calculateBalance() {
    const totalAmount = parseFloat($('#totalAmount').text().replace('Rs. ', '')) || 0;
    const paidAmount = parseFloat($('#paidAmount').val()) || 0;
    const balance = totalAmount - paidAmount;

    $('#balanceAmount').text('Rs. ' + balance.toFixed(2));
}

function loadCustomerDetails(customerId) {
    if (!customerId) return;

    $.get('/sale/customer/' + customerId + '/details', function (data) {
        $('#customerEmail').text(data.email || '-');
        $('#customerPhone').text(data.phone || '-');
        $('#customerBalance').text('Rs. ' + data.balance.toFixed(2));
    });
}

$(function () {
    // Load existing items
    existingItems.forEach((item, idx) => {
        addItemRow(
            { id: item.product_id },
            item.quantity,
            item.unit_price,
            item.discount_percent
        );
    });

    // Load customer details on page load
    const customerId = $('#customerSelect').val();
    if (customerId) {
        loadCustomerDetails(customerId);
    }

    toggleBankAccountField();

    // Calculate totals on page load
    setTimeout(calculateTotals, 100);
});
</script>
@endpush
