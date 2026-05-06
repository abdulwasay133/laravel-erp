@extends('layouts.app')

@section('title', isset($bank) ? 'Edit Acount' : 'Add Acount')
@section('page-title', isset($bank) ? 'Edit Acount' : 'Add Acount')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('bank.index') }}" class="text-decoration-none text-muted">Acount</a></li>
    <li class="breadcrumb-item active">{{ isset($bank) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-9">

        <form action="{{ isset($bank) ? route('bank.update', $bank->id) : route('bank.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($bank)) @method('PUT') @endif

            {{-- ── Basic Information ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-vcard text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Basic Information</h6>
                        <p class="card-subtitle">Primary Acount details</p>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name"
                                   class="form-control @error('bank_name') is-invalid @enderror"
                                   value="{{ old('bank_name', $bank->bank_name ?? '') }}"
                                   placeholder="e.g. HBL, UBL, MCB">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Account Title <span class="text-danger">*</span></label>
                            <input type="text" name="account_title"
                                   class="form-control @error('account_title') is-invalid @enderror"
                                   value="{{ old('account_title', $bank->account_title ?? '') }}"
                                   placeholder="e.g. Main Account">
                            @error('account_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Branch Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                {{-- <span class="input-group-text"><i class="bi bi-envelope"></i></span> --}}
                                <input type="text" name="branch_code"
                                       class="form-control @error('branch_code') is-invalid @enderror"
                                       value="{{ old('branch_code', $bank->branch_code ?? '') }}"
                                       placeholder="e.g. 987">
                                @error('branch_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Account Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <input type="text" name="account_number"
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       value="{{ old('account_number', $bank->account_number ?? '') }}"
                                       placeholder="0000-0000000-0">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        

                        <div class="col-12">
                            <label class="form-label fw-600">Opening Balance</label><span class="text-muted">(optional)</span>
                            <input type="number" name="opening_balance"
                                   class="form-control"
                                   value="{{ old('opening_balance', $bank->opening_balance ?? '') }}"
                                   placeholder="0.00">
                        </div>


                    </div>

                </div>
            </div>

                     


            {{-- ── Buttons ── --}}
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('bank.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($bank) ? 'Update Account' : 'Save Account' }}
                </button>
            </div>

        </form>
    </div>
</div>

@endsection