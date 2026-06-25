@extends('layouts.app')

@section('title', 'Record Supplier Payment')
@section('page-title', 'Record Supplier Payment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supplier-payments.index') }}" class="text-decoration-none text-muted">Supplier Payments</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Please fix the following errors:
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('supplier-payments.store') }}" method="POST">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-credit-card text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Payment Details</h6>
                        <p class="card-subtitle">Record a supplier payment</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Voucher Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="voucher_no" id="voucher_no"
                                       class="form-control @error('voucher_no') is-invalid @enderror"
                                       value="{{ old('voucher_no') }}" readonly>
                                <button type="button" class="btn btn-outline-secondary" id="generateBtn">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date"
                                   class="form-control @error('payment_date') is-invalid @enderror"
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Select Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplierSelect"
                                    class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="">-- Select Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->first_name }} {{ $supplier->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div id="supplierDueInfo" class="mt-2" style="display: none;">
                                <small class="text-muted">Due Amount:</small>
                                <strong id="supplierDueAmount" class="text-danger">0.00</strong>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Payment Type <span class="text-danger">*</span></label>
                            <select name="payment_type"
                                    class="form-select @error('payment_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="credit" {{ old('payment_type') == 'credit' ? 'selected' : '' }}>Credit</option>
                                <option value="debit" {{ old('payment_type') == 'debit' ? 'selected' : '' }}>Debit</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod"
                                    class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="">-- Select Method --</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="account" {{ old('payment_method') == 'account' ? 'selected' : '' }}>Account</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="bankAccountDiv" style="display: none;">
                            <label class="form-label fw-600">Bank Account <span class="text-danger" id="bankRequired">*</span></label>
                            <select name="bank_account_id"
                                    class="form-select @error('bank_account_id') is-invalid @enderror">
                                <option value="">-- Select Account --</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->bank_name }} - {{ $account->account_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-pound"></i></span>
                                <input type="number" name="amount" step="0.01" min="0.01"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount') }}" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Reference</label>
                            <input type="text" name="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference') }}"
                                   placeholder="e.g. Check #, Invoice #">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-600">Description</label>
                            <textarea name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3" placeholder="Payment details...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('supplier-payments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Record Payment
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#generateBtn').click(function () {
            $.ajax({
                url: '{{ route("supplier-payments.generate-voucher-no") }}',
                type: 'GET',
                success: function (response) {
                    $('#voucher_no').val(response.voucher_no);
                }
            });
        });

        if ($('#voucher_no').val() === '') {
            $('#generateBtn').click();
        }

        $('#paymentMethod').change(function () {
            if ($(this).val() === 'account') {
                $('#bankAccountDiv').show();
                $('select[name="bank_account_id"]').prop('required', true);
            } else {
                $('#bankAccountDiv').hide();
                $('select[name="bank_account_id"]').prop('required', false);
            }
        }).trigger('change');

        $('#supplierSelect').change(function () {
            const supplierId = $(this).val();
            if (!supplierId) {
                $('#supplierDueInfo').hide();
                return;
            }
            $.ajax({
                url: '/supplier/view/' + supplierId,
                type: 'GET',
                success: function (data) {
                    const balance = parseFloat(data.balance) || 0;
                    $('#supplierDueAmount').text(balance.toFixed(2));
                    $('#supplierDueInfo').show();
                },
                error: function () {
                    $('#supplierDueInfo').hide();
                }
            });
        });

        if ($('#supplierSelect').val()) {
            $('#supplierSelect').trigger('change');
        }
    });
</script>
@endpush
