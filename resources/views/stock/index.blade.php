@extends('layouts.app')

@section('title', 'Stock')
@section('page-title', 'Stock Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Stock</li>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Products in Stock</h6>
                        <h5 class="mb-0 fw-bold">{{ $stats['total_products'] }}</h5>
                    </div>
                    <i class="bi bi-box fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Total Stock Qty</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($stats['total_stock_qty']) }}</h5>
                    </div>
                    <i class="bi bi-boxes fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Stock Sale Value</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_stock_sale_value'], 2) }}</h5>
                    </div>
                    <i class="bi bi-currency-dollar fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-1 text-white-50">Stock Purchase Value</h6>
                        <h5 class="mb-0 fw-bold">Rs. {{ number_format($stats['total_stock_purchase_value'], 2) }}</h5>
                    </div>
                    <i class="bi bi-cart fs-1 text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h6 class="card-title">Stock Report</h6>
        <p class="card-subtitle">View product stock information</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="stockTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Product Name</th>
                        <th>Sale Price</th>
                        <th>Purchase Price</th>
                        <th class="text-center">In Qty</th>
                        <th class="text-center">Out Qty</th>
                        <th class="text-center">Stock</th>
                        <th>Stock Sale Price</th>
                        <th>Stock Purchase Price</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr style="background-color: #f8f9fa; font-weight: bold;">
                        <th colspan="4" class="text-end">Totals:</th>
                        <th class="text-center" id="total-in-qty">0</th>
                        <th class="text-center" id="total-out-qty">0</th>
                        <th class="text-center" id="total-stock">0</th>
                        <th id="total-stock-sale-price">Rs. 0.00</th>
                        <th id="total-stock-purchase-price">Rs. 0.00</th>
                    </tr>
                </tfoot>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#stockTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('stock.index') }}",
            type: 'GET',
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'name', name: 'name' },
            { data: 'sale_price', name: 'sale_price' },
            { data: 'purchase_price', name: 'purchase_price' },
            { data: 'in_qty', name: 'in_qty', className: 'text-center' },
            { data: 'out_qty', name: 'out_qty', className: 'text-center' },
            { data: 'stock', name: 'stock', className: 'text-center' },
            { data: 'stock_sale_price', name: 'stock_sale_price' },
            { data: 'stock_purchase_price', name: 'stock_purchase_price' },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[1, 'asc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="bi bi-clipboard me-1"></i> Copy',
                className: 'btn buttons-copy',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'csv',
                text: '<i class="bi bi-filetype-csv me-1"></i> CSV',
                className: 'btn buttons-csv',
                title: 'Stock Report',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel',
                className: 'btn buttons-excel',
                title: 'Stock Report',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF',
                className: 'btn buttons-pdf',
                title: 'Stock Report',
                orientation: 'portrait',
                pageSize: 'A4',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-1"></i> Print',
                className: 'btn buttons-print',
                title: 'Stock Report',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7, 8] }
            },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '',
            searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: {
                previous: '<i class="bi bi-chevron-left"></i>',
                next: '<i class="bi bi-chevron-right"></i>'
            },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No stock data found</div>'
        },
        footerCallback: function(row, data, start, end, display) {
            var api = this.api();
            var json = api.ajax.json();

            if (json && json.totals) {
                $(api.column(4).footer()).html(json.totals.total_in_qty);
                $(api.column(5).footer()).html(json.totals.total_out_qty);
                $(api.column(6).footer()).html(json.totals.total_stock);
                $(api.column(7).footer()).html(json.totals.total_stock_sale_price);
                $(api.column(8).footer()).html(json.totals.total_stock_purchase_price);
            }
        }
    });
});
</script>
@endpush
