@extends('layouts.app')

@section('title', 'POS Refund')
@section('page-title', 'POS Refund — ' . $transaction->receipt_no)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
    <li class="breadcrumb-item"><a href="{{ route('pos.list') }}">Transactions</a></li>
    <li class="breadcrumb-item active">Refund</li>
@endsection

@section('content')

<div class="row">
    <div class="col-md-8">

        {{-- Invoice Summary --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-receipt me-1"></i> {{ $transaction->receipt_no }}</span>
                <span class="text-muted small">{{ $transaction->transaction_at->format('d-m-Y h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <strong>Customer:</strong> {{ $transaction->customer_name ?: ($transaction->customer?->first_name ? $transaction->customer->first_name . ' ' . ($transaction->customer->last_name ?? '') : 'Walk-in') }}
                    </div>
                    <div class="col-md-3"><strong>Subtotal:</strong> Rs. {{ number_format($transaction->subtotal, 0) }}</div>
                    <div class="col-md-3"><strong>Total:</strong> Rs. {{ number_format($transaction->grand_total, 0) }}</div>
                </div>
            </div>
        </div>

        {{-- Items to Refund --}}
        <div class="card">
            <div class="card-header"><i class="bi bi-box-seam me-1"></i> Select Items to Refund</div>
            <div class="card-body">
                <form id="refundForm">
                    <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">

                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                                <th>Product</th>
                                <th class="text-center">Qty Sold</th>
                                <th class="text-center">Already Refunded</th>
                                <th class="text-center">Refund Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Refund Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transaction->items as $item)
                                @php
                                    $available = $item->quantity - ($item->refunded_quantity ?? 0);
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" name="items[]" value="{{ $item->id }}" class="item-checkbox" data-available="{{ $available }}" data-price="{{ $item->unit_price }}" {{ $available <= 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ (int) $item->quantity }}</td>
                                    <td class="text-center">{{ (int) ($item->refunded_quantity ?? 0) }}</td>
                                    <td class="text-center">
                                        <input type="number" name="qty_{{ $item->id }}" class="form-control form-control-sm refund-qty" style="width:70px;margin:0 auto;" value="{{ min(1, $available) }}" min="1" max="{{ $available }}" {{ $available <= 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td class="text-end">Rs. {{ number_format($item->unit_price, 0) }}</td>
                                    <td class="text-end refund-amount">Rs. 0</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="6" class="text-end">Total Refund:</td>
                                <td class="text-end" id="totalRefundAmount">Rs. 0</td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Reason for Refund <span class="text-danger">*</span></label>
                        <textarea name="reason" id="refundReason" class="form-control" rows="2" placeholder="Enter refund reason..." required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-danger" id="processRefundBtn"><i class="bi bi-arrow-return-left me-1"></i> Process Refund</button>
                        <a href="{{ route('pos.list') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Payment Summary Sidebar --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-credit-card me-1"></i> Payment Details</div>
            <div class="card-body">
                @foreach($transaction->payments as $p)
                    <div class="d-flex justify-content-between mb-1">
                        <span class="badge bg-{{ $p->method == 'cash' ? 'success' : ($p->method == 'bank' ? 'primary' : 'warning') }}">{{ ucfirst($p->method) }}</span>
                        <span>Rs. {{ number_format($p->amount, 0) }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Paid</span>
                    <span>Rs. {{ number_format($transaction->tendered_amount, 0) }}</span>
                </div>
                @if($transaction->change_amount > 0)
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Change</span>
                        <span>Rs. {{ number_format($transaction->change_amount, 0) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Select All
    $('#selectAll').on('change', function () {
        $('.item-checkbox:not(:disabled)').prop('checked', $(this).is(':checked')).trigger('change');
    });

    // Calculate refund amounts
    function calcRefund() {
        let total = 0;
        $('.item-checkbox:checked').each(function () {
            const row = $(this).closest('tr');
            const price = parseFloat($(this).data('price')) || 0;
            const qty = parseFloat(row.find('.refund-qty').val()) || 0;
            const amount = price * qty;
            row.find('.refund-amount').text('Rs. ' + amount.toLocaleString());
            total += amount;
        });
        $('#totalRefundAmount').text('Rs. ' + total.toLocaleString());
    }

    $(document).on('change', '.item-checkbox', calcRefund);
    $(document).on('input', '.refund-qty', function () {
        const max = parseFloat($(this).attr('max')) || 0;
        let val = parseFloat($(this).val()) || 1;
        if (val < 1) val = 1;
        if (val > max) val = max;
        $(this).val(val);
        calcRefund();
    });

    // Submit
    $('#refundForm').on('submit', function (e) {
        e.preventDefault();

        const checked = $('.item-checkbox:checked');
        if (!checked.length) {
            Swal.fire('No Items', 'Select at least one item to refund.', 'warning');
            return;
        }
        const reason = $('#refundReason').val().trim();
        if (!reason) {
            Swal.fire('Reason Required', 'Please enter a reason for the refund.', 'warning');
            return;
        }

        const items = [];
        checked.each(function () {
            const row = $(this).closest('tr');
            const id = $(this).val();
            const qty = parseFloat(row.find('.refund-qty').val()) || 1;
            items.push({ item_id: id, quantity: qty });
        });

        $('#processRefundBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Processing...');

        $.ajax({
            url: '/api/pos/transaction/{{ $transaction->id }}/refund-items',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ items: items, reason: reason }),
            success: function (res) {
                Swal.fire({
                    title: 'Refund Processed',
                    text: res.message || 'Refund completed successfully.',
                    icon: 'success',
                }).then(function () {
                    window.location.href = '{{ route('pos.list') }}';
                });
            },
            error: function (xhr) {
                $('#processRefundBtn').prop('disabled', false).html('<i class="bi bi-arrow-return-left me-1"></i> Process Refund');
                Swal.fire('Error', xhr.responseJSON?.message || 'Refund failed.', 'error');
            }
        });
    });
});
</script>
@endpush
