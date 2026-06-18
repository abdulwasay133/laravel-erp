@extends('layouts.app')

@section('title', 'Pay Salary')
@section('page-title', 'Pay Salary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('salary.index') }}" class="text-decoration-none text-muted">Salaries</a></li>
    <li class="breadcrumb-item active">Pay</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('salary.store') }}" method="POST">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-currency-dollar text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Salary Payment</h6>
                        <p class="card-subtitle">Record monthly salary payment</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required id="employeeSelect">
                                <option value="">-- Select Employee --</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}" data-salary="{{ $emp->salary_amount }}"
                                        {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->employee_code }} - {{ $emp->first_name }} {{ $emp->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600">Salary Month <span class="text-danger">*</span></label>
                            <input type="month" name="salary_month" class="form-control" required
                                   value="{{ old('salary_month', date('Y-m')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-600">Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0.01" class="form-control" required
                                   value="{{ old('amount', '') }}" id="amountField" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required
                                   value="{{ old('payment_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="form-select" required>
                                <option value="">-- Select Method --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ old('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                            </select>
                        </div>
                        <div class="col-md-4" id="bankAccountDiv" style="display:none;">
                            <label class="form-label fw-600">Bank Account <span class="text-danger">*</span></label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">-- Select Bank Account --</option>
                                @foreach($bankAccounts as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }} - {{ $bank->account_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Reference</label>
                            <input type="text" name="reference" class="form-control"
                                   value="{{ old('reference') }}" placeholder="Transaction ID or note">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Optional description...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('salary.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Pay Salary
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function toggleBank() {
        const isBank = $('#paymentMethod').val() === 'bank';
        $('#bankAccountDiv').toggle(isBank);
    }
    $('#paymentMethod').on('change', toggleBank);
    toggleBank();

    $('#employeeSelect').on('change', function () {
        const selected = $(this).find(':selected');
        const salary = selected.data('salary');
        if (salary) {
            $('#amountField').val(salary);
        }
    });

    @if(request('employee_id'))
        $('#employeeSelect').trigger('change');
    @endif
});
</script>
@endpush
