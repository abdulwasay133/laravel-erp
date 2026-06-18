@extends('layouts.app')

@section('title', 'Create Opening Balance')
@section('page-title', 'Create Opening Balance')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('opening-balances.index') }}">Opening Balances</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Create Opening Balance</h6>
                <p class="card-subtitle">Add opening balance entry</p>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('opening-balances.store') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                            <input type="text" name="voucher_no" id="voucher_no" class="form-control @error('voucher_no') is-invalid @enderror" value="{{ old('voucher_no') }}" placeholder="e.g., OB-000001" required readonly>
                            @error('voucher_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                            <input type="date" name="voucher_date" class="form-control @error('voucher_date') is-invalid @enderror" value="{{ old('voucher_date', date('Y-m-d')) }}" required>
                            @error('voucher_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Account Head <span class="text-danger">*</span></label>
                        <select name="chart_of_account_id" class="form-select @error('chart_of_account_id') is-invalid @enderror" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ old('chart_of_account_id') == $account->id ? 'selected' : '' }}>{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('chart_of_account_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" step="0.01" min="0" placeholder="0.00" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Optional description">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('opening-balances.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Opening Balance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate voucher number
    $.get('{{ route('opening-balances.generate-voucher-no') }}', function(data) {
        $('#voucher_no').val(data.voucher_no);
    });
});
</script>
@endpush
