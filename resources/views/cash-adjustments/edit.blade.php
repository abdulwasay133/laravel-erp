@extends('layouts.app')

@section('title', 'Edit Cash Adjustment')
@section('page-title', 'Edit Cash Adjustment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('cash-adjustments.index') }}" class="text-decoration-none text-muted">Cash Adjustments</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')

{{-- Flash Messages --}}
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

        <form action="{{ route('cash-adjustments.update', $cashAdjustment->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ── Cash Adjustment Information ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-receipt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Edit Cash Adjustment</h6>
                        <p class="card-subtitle">Modify cash adjustment details</p>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        {{-- Voucher Number --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Voucher Number <span class="text-danger">*</span></label>
                            <input type="text" name="voucher_no"
                                   class="form-control @error('voucher_no') is-invalid @enderror"
                                   value="{{ old('voucher_no', $cashAdjustment->voucher_no) }}"
                                   placeholder="CA-000001" required>
                            @error('voucher_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Adjustment Date --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Adjustment Date <span class="text-danger">*</span></label>
                            <input type="date" name="adjustment_date"
                                   class="form-control @error('adjustment_date') is-invalid @enderror"
                                   value="{{ old('adjustment_date', $cashAdjustment->adjustment_date->format('Y-m-d')) }}"
                                   required>
                            @error('adjustment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Account Selection --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Chart of Account <span class="text-danger">*</span></label>
                            <select name="chart_of_account_id"
                                    class="form-select @error('chart_of_account_id') is-invalid @enderror"
                                    required>
                                <option value="">-- Select Account --</option>
                                @if($accounts)
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}" 
                                            {{ old('chart_of_account_id', $cashAdjustment->chart_of_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('chart_of_account_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Adjustment Type --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Adjustment Type <span class="text-danger">*</span></label>
                            <select name="adjustment_type"
                                    class="form-select @error('adjustment_type') is-invalid @enderror"
                                    required>
                                <option value="">-- Select Type --</option>
                                <option value="increase" 
                                    {{ old('adjustment_type', $cashAdjustment->adjustment_type) == 'increase' ? 'selected' : '' }}>
                                    <i class="bi bi-arrow-up"></i> Increase
                                </option>
                                <option value="decrease"
                                    {{ old('adjustment_type', $cashAdjustment->adjustment_type) == 'decrease' ? 'selected' : '' }}>
                                    <i class="bi bi-arrow-down"></i> Decrease
                                </option>
                            </select>
                            @error('adjustment_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Amount --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-pound"></i></span>
                                <input type="number" name="amount" step="0.01" min="0"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', $cashAdjustment->amount) }}"
                                       placeholder="0.00" required>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Reference --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Reference</label>
                            <input type="text" name="reference"
                                   class="form-control @error('reference') is-invalid @enderror"
                                   value="{{ old('reference', $cashAdjustment->reference) }}"
                                   placeholder="e.g. Invoice #, Check #">
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label fw-600">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror"
                                      rows="4" placeholder="Enter adjustment details/reason...">{{ old('description', $cashAdjustment->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Info Box --}}
                        <div class="col-12">
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Note:</strong> Editing this record will adjust the account balances accordingly.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Action Buttons ── --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('cash-adjustments.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Adjustment
                        </button>
                    </div>
                </div>
            </div>

        </form>

    </div>
</div>

@endsection
