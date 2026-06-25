@extends('layouts.app')

@section('title', 'New Purchase Return')
@section('page-title', 'Supplier Purchase Return')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('purchase-returns.index') }}" class="text-decoration-none text-muted">Purchase Returns</a></li>
    <li class="breadcrumb-item active">New Return</li>
@endsection

@section('content')
<form method="POST" action="{{ route('purchase-returns.store') }}" id="purchaseReturnForm">
    @csrf
    <input type="hidden" name="purchase_id" id="purchaseId" value="{{ old('purchase_id') }}">

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">Find Purchase Invoice</h6>
            <p class="card-subtitle mb-0">Enter purchase reference / invoice number</p>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Reference / Invoice No <span class="text-danger">*</span></label>
                    <input type="text" id="refLookup" class="form-control" placeholder="e.g. PO-000001">
                </div>
                <div class="col-md-3">
                    <button type="button" id="lookupBtn" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i> Find Invoice
                    </button>
                </div>
            </div>
            <div id="lookupError" class="alert alert-danger mt-3 d-none"></div>
        </div>
    </div>

    <div id="invoiceDetails" class="d-none">
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Purchase Details</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><small class="text-muted">Ref No</small><div class="fw-semibold" id="detailRefNo">-</div></div>
                    <div class="col-md-3"><small class="text-muted">Order Date</small><div class="fw-semibold" id="detailOrderDate">-</div></div>
                    <div class="col-md-3"><small class="text-muted">Supplier</small><div class="fw-semibold" id="detailSupplier">-</div></div>
                    <div class="col-md-3"><small class="text-muted">Status</small><div class="fw-semibold" id="detailStatus">-</div></div>
                    <div class="col-md-3"><small class="text-muted">Grand Total</small><div class="fw-semibold" id="detailTotal">0.00</div></div>
                    <div class="col-md-3"><small class="text-muted">Paid Amount</small><div class="fw-semibold text-success" id="detailPaid">0.00</div></div>
                    <div class="col-md-3"><small class="text-muted">Due Amount</small><div class="fw-semibold text-danger" id="detailDue">0.00</div></div>
                    <div class="col-md-3"><small class="text-muted">Discount</small><div class="fw-semibold" id="detailDiscount">0.00</div></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Return Items</h6>
                <p class="card-subtitle mb-0">Select return quantity (max = remaining quantity)</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Batch</th>
                                <th class="text-end">Purchase Qty</th>
                                <th class="text-end">Returned</th>
                                <th class="text-end">Remaining</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">Line Total</th>
                                <th class="text-end">Return Qty</th>
                                <th class="text-end">Return Amount</th>
                            </tr>
                        </thead>
                        <tbody id="returnItemsBody"></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="8" class="text-end">Total Return Amount</th>
                                <th class="text-end" id="totalReturnAmount">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h6 class="card-title mb-0">Return Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Return No</label>
                        <input type="text" name="return_no" class="form-control" value="{{ $returnNo }}" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Return Date <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Return Reason <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="Reason for return" required value="{{ old('reason') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Refund Received</label>
                        <select name="refund_method" id="refundMethod" class="form-select">
                            <option value="none">Credit Only (No Refund Received)</option>
                            <option value="cash">Cash Refund Received</option>
                            <option value="bank_transfer">Bank Refund Received</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="bankAccountWrap" style="display:none;">
                        <label class="form-label">Bank Account</label>
                        <select name="bank_account_id" class="form-select">
                            <option value="">Select Bank</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="1">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-1"></i> Process Return
            </button>
            <a href="{{ route('purchase-returns.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    function formatAmount(v) {
        return parseFloat(v || 0).toFixed(2);
    }

    function renderItems(items) {
        const body = $('#returnItemsBody').empty();
        let itemIndex = 0;

        items.forEach(function (item) {
            if (item.remaining_quantity <= 0) return;

            const unitLine = item.purchase_quantity > 0 ? item.subtotal / item.purchase_quantity : 0;
            const row = `
                <tr>
                    <td>${item.product_name}</td>
                    <td>${item.batch_number}</td>
                    <td class="text-end">${item.purchase_quantity}</td>
                    <td class="text-end">${item.returned_quantity}</td>
                    <td class="text-end"><span class="badge bg-info">${item.remaining_quantity}</span></td>
                    <td class="text-end">${formatAmount(item.unit_cost)}</td>
                    <td class="text-end">${formatAmount(item.subtotal)}</td>
                    <td class="text-end">
                        <input type="hidden" name="items[${itemIndex}][purchase_item_id]" value="${item.purchase_item_id}">
                        <input type="number" name="items[${itemIndex}][return_quantity]" class="form-control form-control-sm return-qty text-end"
                               min="0" max="${item.remaining_quantity}" value="0"
                               data-unit-line="${unitLine}" data-remaining="${item.remaining_quantity}">
                    </td>
                    <td class="text-end return-line-total">0.00</td>
                </tr>`;
            body.append(row);
            itemIndex++;
        });

        updateTotals();
    }

    function updateTotals() {
        let total = 0;
        $('.return-qty').each(function () {
            const qty = parseInt($(this).val() || 0, 10);
            const unitLine = parseFloat($(this).data('unit-line') || 0);
            const lineTotal = qty * unitLine;
            $(this).closest('tr').find('.return-line-total').text(formatAmount(lineTotal));
            total += lineTotal;
        });
        $('#totalReturnAmount').text(formatAmount(total));
    }

    $(document).on('input', '.return-qty', function () {
        const max = parseInt($(this).data('remaining'), 10);
        let val = parseInt($(this).val() || 0, 10);
        if (val > max) {
            val = max;
            $(this).val(max);
            alert('Return quantity cannot exceed remaining quantity (' + max + ').');
        }
        if (val < 0) $(this).val(0);
        updateTotals();
    });

    $('#lookupBtn').on('click', function () {
        const refNo = $('#refLookup').val().trim();
        if (!refNo) {
            alert('Please enter a purchase reference number.');
            return;
        }

        $.get('{{ route('purchase-returns.lookup') }}', { ref_no: refNo })
            .done(function (response) {
                $('#lookupError').addClass('d-none');
                $('#invoiceDetails').removeClass('d-none');
                $('#purchaseId').val(response.purchase.id);

                $('#detailRefNo').text(response.purchase.ref_no);
                $('#detailOrderDate').text(response.purchase.order_date);
                $('#detailSupplier').text(response.purchase.supplier_name);
                $('#detailStatus').text(response.purchase.status);
                $('#detailTotal').text(formatAmount(response.purchase.grand_total));
                $('#detailPaid').text(formatAmount(response.purchase.paid_amount));
                $('#detailDue').text(formatAmount(response.purchase.due_amount));
                $('#detailDiscount').text(formatAmount(response.purchase.discount));

                renderItems(response.items);
            })
            .fail(function (xhr) {
                $('#invoiceDetails').addClass('d-none');
                $('#lookupError').removeClass('d-none').text(xhr.responseJSON?.message || 'Purchase invoice not found.');
            });
    });

    $('#refundMethod').on('change', function () {
        $('#bankAccountWrap').toggle($(this).val() === 'bank_transfer');
    });

    $('#purchaseReturnForm').on('submit', function (e) {
        let hasQty = false;
        $('.return-qty').each(function () {
            if (parseInt($(this).val() || 0, 10) > 0) hasQty = true;
        });
        if (!hasQty) {
            e.preventDefault();
            alert('Please enter return quantity for at least one item.');
        }
    });

    @if($refNo)
        $('#refLookup').val('{{ $refNo }}');
        $('#lookupBtn').trigger('click');
    @endif
});
</script>
@endpush
