@extends('layouts.app')

@section('title', 'Salary Management')
@section('page-title', 'Salary Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Salaries</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h6 class="card-title">Pending Salaries</h6>
            <p class="card-subtitle">Employees whose salary has not been paid yet</p>
        </div>
        <div>
            <span class="badge bg-warning text-dark fs-6 me-2" id="pendingCount">0</span>
            <a href="{{ route('salary.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Pay Salary
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="pendingTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Employee Code</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th class="text-end">Salary Amount</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody id="pendingBody">
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox d-block fs-4 mb-1"></i>Loading pending salaries...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h6 class="card-title">Salary Payment History</h6>
            <p class="card-subtitle">All recorded salary payments</p>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="salaryTable" class="table table-hover align-middle w-100">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Employee</th>
                        <th>Salary Month</th>
                        <th class="text-end">Amount</th>
                        <th>Payment Date</th>
                        <th>Method</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadPending() {
    $.get('{{ route('salary.pending') }}', function (res) {
        const tbody = $('#pendingBody');
        const count = res.pending.length;
        $('#pendingCount').text(count);

        if (count === 0) {
            tbody.html('<tr><td colspan="7" class="text-center text-muted py-4"><i class="bi bi-check-circle d-block fs-4 mb-1 text-success"></i>All salaries are paid for this month.</td></tr>');
            return;
        }

        let html = '';
        $.each(res.pending, function (i, emp) {
            html += '<tr>' +
                '<td>' + (i + 1) + '</td>' +
                '<td>' + emp.employee_code + '</td>' +
                '<td>' + emp.first_name + ' ' + emp.last_name + '</td>' +
                '<td>' + (emp.department || '-') + '</td>' +
                '<td>' + (emp.designation || '-') + '</td>' +
                '<td class="text-end">' + parseFloat(emp.salary_amount).toLocaleString('en-US', {minimumFractionDigits: 2}) + '</td>' +
                '<td class="text-center"><a href="{{ route('salary.create') }}?employee_id=' + emp.id + '" class="btn btn-sm btn-success"><i class="bi bi-currency-dollar"></i> Pay Now</a></td>' +
                '</tr>';
        });
        tbody.html(html);
    });
}

$(function () {
    loadPending();

    $('#salaryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('salary.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'employee', name: 'employee' },
            { data: 'salary_month', name: 'salary_month' },
            { data: 'amount', name: 'amount', className: 'text-end' },
            { data: 'payment_date', name: 'payment_date' },
            { data: 'payment_method', name: 'payment_method' },
        ],
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order: [[4, 'desc']],
        dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',
        buttons: [
            { extend: 'copy', text: '<i class="bi bi-clipboard me-1"></i> Copy', className: 'btn buttons-copy', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i> CSV', className: 'btn buttons-csv', title: 'Salary Payments', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i> Excel', className: 'btn buttons-excel', title: 'Salary Payments', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i> PDF', className: 'btn buttons-pdf', title: 'Salary Payments', orientation: 'portrait', pageSize: 'A4', exportOptions: { columns: [0,1,2,3,4,5] } },
            { extend: 'print', text: '<i class="bi bi-printer me-1"></i> Print', className: 'btn buttons-print', title: 'Salary Payments', exportOptions: { columns: [0,1,2,3,4,5] } },
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm text-primary me-2"></span> Loading...',
            search: '', searchPlaceholder: 'Search ...',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            paginate: { previous: '<i class="bi bi-chevron-left"></i>', next: '<i class="bi bi-chevron-right"></i>' },
            zeroRecords: '<div class="text-center py-3 text-muted"><i class="bi bi-inbox d-block fs-4 mb-1"></i>No salary payments found</div>'
        },
    });
});
</script>
@endpush
