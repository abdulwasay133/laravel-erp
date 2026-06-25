@extends('layouts.app')

@section('title', 'Pay Commission')
@section('page-title', 'Pay Commission')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('commission-payments.index') }}" class="text-decoration-none text-muted">Commission Payments</a></li>
    <li class="breadcrumb-item active">New Payment</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-10">
        <form action="{{ route('commission-payments.store') }}" method="POST" id="paymentForm">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-cash-coin text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Payment Details</h6>
                        <p class="card-subtitle">Commission settlement information</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-600">Order Booker <span class="text-danger">*</span></label>
                            <select name="order_booker_id" id="orderBookerSelect" class="form-select" required
                                    onchange="loadCommissions(this.value)">
                                <option value="">-- Select Order Booker --</option>
                                @foreach($orderBookers as $booker)
                                    <option value="{{ $booker->id }}">
                                        {{ $booker->first_name }} {{ $booker->last_name }}
                                        ({{ $booker->department ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="e.g. Transaction ID">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Any notes...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Total Payable</label>
                            <div class="form-control-plaintext fw-bold fs-5 text-primary" id="totalPayable">Rs. 0.00</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4" id="commissionsCard" style="display: none;">
                <div class="card-header">
                    <i class="bi bi-list-check text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Unpaid Commissions</h6>
                        <p class="card-subtitle">Select commissions to pay</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle w-100" id="commissionsTable">
                            <thead>
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                    </th>
                                    <th>Invoice</th>
                                    <th>Sale Date</th>
                                    <th>Sale Amount</th>
                                    <th>Rate</th>
                                    <th>Commission Amount</th>
                                </tr>
                            </thead>
                            <tbody id="commissionsBody">
                                <tr id="noCommissionsRow">
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox d-block fs-2 mb-2"></i>
                                        Select an order booker to load commissions
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot id="commissionsFooter" style="display: none;">
                                <tr class="table-light fw-bold">
                                    <td></td>
                                    <td colspan="4" class="text-end">Total:</td>
                                    <td id="footerTotal">Rs. 0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('commission-payments.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Pay Commission
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const commissionUrlTemplate = '{{ route("commission-payments.commissions", ["orderBookerId" => ":id"]) }}';
let selectedCommissions = [];

function loadCommissions(orderBookerId) {
    const card = document.getElementById('commissionsCard');
    const body = document.getElementById('commissionsBody');
    const footer = document.getElementById('commissionsFooter');

    if (!orderBookerId) {
        card.style.display = 'none';
        return;
    }

    card.style.display = 'block';
    body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading commissions...</td></tr>';
    footer.style.display = 'none';

    $.get(commissionUrlTemplate.replace(':id', orderBookerId), function (data) {
        if (!data || data.length === 0) {
            body.innerHTML = '<tr id="noCommissionsRow"><td colspan="6" class="text-center text-muted py-4"><i class="bi bi-check-circle d-block fs-2 mb-2 text-success"></i>No pending commissions for this booker</td></tr>';
            footer.style.display = 'none';
            document.getElementById('totalPayable').textContent = 'Rs. 0.00';
            return;
        }

        let html = '';
        data.forEach(function (c) {
            html += '<tr>';
            html += '<td><input type="checkbox" name="commission_ids[]" value="' + c.id + '" class="commission-checkbox" onchange="updateTotal()" checked></td>';
            html += '<td>' + c.invoice_no + '</td>';
            html += '<td>' + c.sale_date + '</td>';
            html += '<td>Rs. ' + parseFloat(c.sale_amount).toLocaleString('en', {minimumFractionDigits: 2}) + '</td>';
            html += '<td>' + parseFloat(c.commission_rate).toFixed(2) + '%</td>';
            html += '<td>Rs. ' + parseFloat(c.commission_amount).toLocaleString('en', {minimumFractionDigits: 2}) + '</td>';
            html += '</tr>';
        });
        body.innerHTML = html;
        footer.style.display = '';
        updateTotal();
    }).fail(function () {
        body.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Failed to load commissions. Please try again.</td></tr>';
    });
}

function toggleSelectAll(source) {
    document.querySelectorAll('.commission-checkbox').forEach(function (cb) {
        cb.checked = source.checked;
    });
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.commission-checkbox:checked').forEach(function (cb) {
        const row = cb.closest('tr');
        const amountText = row.querySelector('td:last-child').textContent;
        const cleaned = amountText.replace(/Rs\.\s*/g, '').replace(/,/g, '');
        const amount = parseFloat(cleaned) || 0;
        total += amount;
    });
    document.getElementById('footerTotal').textContent = 'Rs. ' + total.toLocaleString('en', {minimumFractionDigits: 2});
    document.getElementById('totalPayable').textContent = 'Rs. ' + total.toLocaleString('en', {minimumFractionDigits: 2});
}

$(function () {
    $('#paymentForm').on('submit', function (e) {
        const checked = document.querySelectorAll('.commission-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'No Commissions Selected', text: 'Please select at least one commission to pay.' });
        }
    });
});
</script>
@endpush
