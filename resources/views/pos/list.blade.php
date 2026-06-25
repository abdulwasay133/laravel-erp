@extends('layouts.app')

@section('title', 'POS Transactions')
@section('page-title', 'POS Transactions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pos.index') }}">POS</a></li>
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Transactions</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total'] }}</h5>
                    </div>
                    <i class="bi bi-receipt fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Completed</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['completed'] }}</h5>
                    </div>
                    <i class="bi bi-check-circle fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Today's Sales</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['todays_sales'], 0) }}</h5>
                    </div>
                    <i class="bi bi-cash-stack fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Revenue</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_revenue'], 0) }}</h5>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">

        {{-- Filters --}}
        <form method="GET" class="row g-2 mb-3">
            <div class="col">
                <input type="text" name="receipt_no" class="form-control" placeholder="Receipt #" value="{{ request('receipt_no') }}">
            </div>
            <div class="col">
                <div class="date-range-group">
                    <input type="text" class="form-control flatpickr-start" placeholder="Start date">
                    <span class="range-sep">→</span>
                    <input type="text" class="form-control flatpickr-end" placeholder="End date">
                    <input type="hidden" name="date_from" id="startDate" value="{{ request('date_from') }}">
                    <input type="hidden" name="date_to" id="endDate" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col">
                <select name="customer_id" class="form-select">
                    <option value="">All Customers</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->first_name }} {{ $c->last_name }} ({{ $c->phone }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col">
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Credit</option>
                </select>
            </div>
            <div class="col">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="voided" {{ request('status') == 'voided' ? 'selected' : '' }}>Voided</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-1">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                <a href="{{ route('pos.list') }}" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="posTable">
                <thead class="table-light">
                    <tr>
                        <th>Receipt #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Discount</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Change</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                        <tr>
                            <td><strong>{{ $t->receipt_no }}</strong></td>
                            <td>{{ $t->transaction_at->format('d-m-Y h:i A') }}</td>
                            <td>{{ $t->customer_name ?: ($t->customer?->first_name ? $t->customer->first_name . ' ' . ($t->customer->last_name ?? '') : 'Walk-in') }}</td>
                            <td>{{ $t->items->count() }}</td>
                            <td>Rs. {{ number_format($t->subtotal, 0) }}</td>
                            <td>Rs. {{ number_format($t->discount_amount, 0) }}</td>
                            <td><strong>Rs. {{ number_format($t->grand_total, 0) }}</strong></td>
                            <td>Rs. {{ number_format($t->tendered_amount, 0) }}</td>
                            <td>Rs. {{ number_format($t->change_amount, 0) }}</td>
                            <td>
                                @foreach($t->payments as $p)
                                    <span class="badge bg-{{ $p->method == 'cash' ? 'success' : ($p->method == 'bank' ? 'primary' : 'warning') }} me-1">{{ ucfirst($p->method) }}</span>
                                @endforeach
                            </td>
                            <td>
                                <span class="badge bg-{{ $t->status == 'completed' ? 'success' : ($t->status == 'voided' ? 'secondary' : 'danger') }}">
                                    {{ ucfirst($t->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="bi bi-list"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="/api/pos/receipt/{{ $t->id }}/print" target="_blank"><i class="bi bi-receipt me-2"></i>Print Receipt</a></li>
                                        <li><a class="dropdown-item" href="/api/pos/receipt/{{ $t->id }}/pdf" target="_blank"><i class="bi bi-file-pdf me-2"></i>PDF</a></li>
                                        @if($t->status === 'completed')
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-secondary void-btn" href="#" data-id="{{ $t->id }}"><i class="bi bi-x-circle me-2"></i>Void</a></li>
                                            <li><a class="dropdown-item text-danger" href="{{ route('pos.refund', $t->id) }}"><i class="bi bi-arrow-return-left me-2"></i>Refund</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="text-center text-muted py-4">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $transactions->links() }}
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.void-btn', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        Swal.fire({
            title: 'Void Transaction?',
            text: 'This will reverse inventory and accounting entries.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, void it',
            confirmButtonColor: '#6c757d',
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/api/pos/transaction/' + id + '/void')
                    .done(function () {
                        Swal.fire('Voided', 'Transaction voided successfully.', 'success');
                        location.reload();
                    })
                    .fail(function (xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Failed to void', 'error'); });
            }
        });
    });

    $(document).on('click', '.refund-btn', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        Swal.fire({
            title: 'Refund Transaction?',
            text: 'This will refund payments and restore inventory.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, refund',
            confirmButtonColor: '#dc3545',
        }).then(result => {
            if (result.isConfirmed) {
                $.post('/api/pos/transaction/' + id + '/refund')
                    .done(function () {
                        Swal.fire('Refunded', 'Transaction refunded successfully.', 'success');
                        location.reload();
                    })
                    .fail(function (xhr) { Swal.fire('Error', xhr.responseJSON?.message || 'Failed to refund', 'error'); });
            }
        });
    });
});
</script>
@endpush
