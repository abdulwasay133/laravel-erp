@extends('layouts.app')

@section('title', isset($supplier) ? 'Edit Supplier' : 'Add Supplier')
@section('page-title', isset($supplier) ? 'Edit Supplier' : 'Add Supplier')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('category.index') }}" class="text-decoration-none text-muted">Supplier</a></li>
    <li class="breadcrumb-item active">{{ isset($supplier) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-9">

        <form action="{{ isset($supplier) ? route('supplier.update', $supplier->id) : route('supplier.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($supplier)) @method('PUT') @endif

            {{-- ── Basic Information ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-vcard text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Basic Information</h6>
                        <p class="card-subtitle">Primary Supplier details</p>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $supplier->first_name ?? '') }}"
                                   placeholder="e.g. Ali">
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $supplier->last_name ?? '') }}"
                                   placeholder="e.g. Raza">
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $supplier->email ?? '') }}"
                                       placeholder="email@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $supplier->phone ?? '') }}"
                                       placeholder="+92 300 0000000">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Customer Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">-- Select Type --</option>
                                <option value="individual" {{ old('type', $supplier->type ?? '') == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business"   {{ old('type', $supplier->type ?? '') == 'business'   ? 'selected' : '' }}>Business</option>
                                <option value="wholesale"  {{ old('type', $supplier->type ?? '') == 'wholesale'  ? 'selected' : '' }}>Wholesale</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select">
                                <option value="1"   {{ old('status', $supplier->status ?? '1') == '1'   ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $supplier->status ?? '')       == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-600">Company Name</label>
                            <input type="text" name="company"
                                   class="form-control"
                                   value="{{ old('company', $supplier->company ?? '') }}"
                                   placeholder="Company / Business name (optional)">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-600">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Any additional notes...">{{ old('notes', $supplier->notes ?? '') }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

                        {{-- ── Address ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Address</h6>
                        <p class="card-subtitle">Billing / shipping address</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-600">Street Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="{{ old('address', $supplier->address ?? '') }}"
                                   placeholder="Street, Block, Area">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">City</label>
                            <input type="text" name="city" class="form-control"
                                   value="{{ old('city', $supplier->city ?? '') }}"
                                   placeholder="Lahore">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Province</label>
                            <select name="province" class="form-select">
                                <option value="">-- Select --</option>
                                @foreach(['Punjab','Sindh','KPK','Balochistan','Islamabad'] as $p)
                                    <option value="{{ $p }}" {{ old('province', $supplier->province ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control"
                                   value="{{ old('postal_code', $supplier->postal_code ?? '') }}"
                                   placeholder="54000">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Financial ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-wallet2 text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Financial Settings</h6>
                        <p class="card-subtitle">Credit limit and payment terms</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Opening Balance (PKR)</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror"
                                       value="{{ old('opening_balance', $supplier->opening_balance ?? 0) }}"
                                       min="1000" step="1000">
                                @error('opening_balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-600">Currency</label>
                            <select name="currency" class="form-select">
                                <option value="PKR" {{ old('currency', $supplier->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR – Pakistani Rupee</option>
                                <option value="USD" {{ old('currency', $supplier->currency ?? '') == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                                <option value="AED" {{ old('currency', $supplier->currency ?? '') == 'AED' ? 'selected' : '' }}>AED – UAE Dirham</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ── Buttons ── --}}
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($supplier) ? 'Update Supplier' : 'Save Supplier' }}
                </button>
            </div>

        </form>
    </div>
</div>

@endsection