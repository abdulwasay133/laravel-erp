@extends('layouts.app')

@section('title', 'Customers')
@section('page-title', 'Customers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customers</li>
@endsection

@push('styles')
<style>
    /* DataTables custom overrides */
    div.dataTables_wrapper div.dataTables_filter input {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 5px 12px;
        font-size: 13px;
    }
    div.dataTables_wrapper div.dataTables_length select {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 4px 8px;
        font-size: 13px;
    }
    div.dataTables_wrapper div.dataTables_info { font-size: 12px; color: #64748b; }
    .dataTables_paginate .page-link { border-radius: 6px !important; font-size: 13px; }
    table.dataTable thead th {
        font-size: 11px; font-weight: 600;
        text-transform: uppercase; letter-spacing: .5px;
        color: #64748b; background: #f8fafc;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    table.dataTable tbody tr:hover { background: #f8fafc; }
    table.dataTable td { vertical-align: middle; font-size: 13.5px; }
</style>
@endpush

@section('content')

{{-- Toolbar --}}
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Customer
    </a>
    <button class="btn btn-outline-secondary" id="exportExcel">
        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
    </button>
    <button class="btn btn-outline-secondary" id="exportPdf">
        <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
    </button>
    <div class="ms-auto d-flex gap-2">
        <select class="form-select form-select-sm" id="filterStatus" style="width:140px;">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
            <option value="Blocked">Blocked</option>
        </select>
        <select class="form-select form-select-sm" id="filterType" style="width:140px;">
            <option value="">All Types</option>
            <option value="Individual">Individual</option>
            <option value="Business">Business</option>
            <option value="Wholesale">Wholesale</option>
        </select>
    </div>
</div>

{{-- DataTable Card --}}
<div class="card">
    <div class="card-header justify-content-between">
        <div>
            <h6 class="card-title">All Customers</h6>
            <p class="card-subtitle">Manage your customer database</p>
        </div>
        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">
            {{ $customers->count() }} total
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="customersTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>City</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $i => $customer)
                    <tr>
                        <td class="text-muted">{{ $i + 1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;border-radius:50%;background:#ede9fe;color:#7c3aed;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                                    {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-600">{{ $customer->first_name }} {{ $customer->last_name }}</div>
                                    @if($customer->company)
                                        <div class="text-muted" style="font-size:11px;">{{ $customer->company }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                        <td>
                            @php $types = ['individual'=>'primary','business'=>'info','wholesale'=>'success'] @endphp
                            <span class="badge bg-{{ $types[$customer->type] ?? 'secondary' }}-subtle text-{{ $types[$customer->type] ?? 'secondary' }} rounded-pill px-3">
                                {{ ucfirst($customer->type) }}
                            </span>
                        </td>
                        <td>{{ $customer->city ?? '—' }}</td>
                        <td class="fw-600">Rs. {{ number_format($customer->balance ?? 0) }}</td>
                        <td>
                            @php $statuses = ['active'=>'success','inactive'=>'secondary','blocked'=>'danger'] @endphp
                            <span class="badge bg-{{ $statuses[$customer->status] ?? 'secondary' }}-subtle text-{{ $statuses[$customer->status] ?? 'secondary' }} rounded-pill px-3">
                                {{ ucfirst($customer->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('customers.show', $customer->id) }}"
                                   class="btn btn-sm btn-outline-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.destroy', $customer->id) }}"
                                      method="POST" class="d-inline delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ── Init DataTable ─────────────────────────────────────
    const table = $('#customersTable').DataTable({
        pageLength: 15,
        lengthMenu: [10, 15, 25, 50, 100],
        order: [[0, 'asc']],
        responsive: true,
        language: {
            search: '',
            searchPlaceholder: 'Search customers...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ customers',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next:     '<i class="bi bi-chevron-right"></i>'
            }
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rtip',
        columnDefs: [
            { orderable: false, targets: [8] }  // Disable sort on Action column
        ]
    });

    // ── Custom column filters ──────────────────────────────
    $('#filterStatus').on('change', function () {
        table.column(7).search(this.value).draw();
    });
    $('#filterType').on('change', function () {
        table.column(4).search(this.value).draw();
    });

    // ── Delete confirmation ────────────────────────────────
    $(document).on('submit', '.delete-form', function (e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
            this.submit();
        }
    });

    // ── Export buttons ─────────────────────────────────────
    // You can add DataTables Buttons extension for proper export
    // For now, simple print/window workaround:
    $('#exportExcel').on('click', function () {
        alert('Add DataTables Buttons extension for Excel export.\nSee SETUP GUIDE.');
    });
    $('#exportPdf').on('click', function () {
        alert('Add DataTables Buttons extension for PDF export.\nSee SETUP GUIDE.');
    });

});
</script>
@endpush