@extends('layouts.app')

@section('title', isset($customer) ? 'Edit Customer' : 'Add Customer')
@section('page-title', isset($customer) ? 'Edit Customer' : 'Add Customer')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none text-muted">Customers</a></li>
    <li class="breadcrumb-item active">{{ isset($customer) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ isset($customer) ? route('customers.update', $customer->id) : route('customers.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($customer)) @method('PUT') @endif

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-vcard text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Basic Information</h6>
                        <p class="card-subtitle">Primary customer details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $customer->first_name ?? '') }}"
                                   placeholder="e.g. Ali">
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $customer->last_name ?? '') }}"
                                   placeholder="e.g. Raza">
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Email Address <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $customer->email ?? '') }}"
                                       placeholder="email@example.com">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       value="{{ old('phone', $customer->phone ?? '') }}"
                                       placeholder="+92 300 0000000">
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Customer Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror">
                                <option value="">-- Select Type --</option>
                                <option value="individual" {{ old('type', $customer->type ?? '') == 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business"   {{ old('type', $customer->type ?? '') == 'business'   ? 'selected' : '' }}>Business</option>
                                <option value="wholesale"  {{ old('type', $customer->type ?? '') == 'wholesale'  ? 'selected' : '' }}>Wholesale</option>
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', $customer->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $customer->status ?? '') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="blocked" {{ old('status', $customer->status ?? '') == 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Company Name</label>
                            <input type="text" name="company"
                                   class="form-control @error('company') is-invalid @enderror"
                                   value="{{ old('company', $customer->company ?? '') }}"
                                   placeholder="Company / Business name (optional)">
                            @error('company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                      placeholder="Any additional notes...">{{ old('notes', $customer->notes ?? '') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

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
                            <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address', $customer->address ?? '') }}"
                                   placeholder="Street, Block, Area">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">City</label>
                            <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city', $customer->city ?? '') }}"
                                   placeholder="Lahore">
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Province</label>
                            <select name="province" class="form-select @error('province') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                @foreach(['Punjab','Sindh','KPK','Balochistan','Islamabad'] as $p)
                                    <option value="{{ $p }}" {{ old('province', $customer->province ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                            @error('province')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                   value="{{ old('postal_code', $customer->postal_code ?? '') }}"
                                   placeholder="54000">
                            @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-wallet2 text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Financial Settings</h6>
                        <p class="card-subtitle">Credit limit and currency</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Opening Balance</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="opening_balance" class="form-control @error('opening_balance') is-invalid @enderror"
                                       value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}"
                                       min="0" step="1000">
                                @error('opening_balance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Currency</label>
                            <select name="currency" class="form-select @error('currency') is-invalid @enderror">
                                <option value="PKR" {{ old('currency', $customer->currency ?? 'PKR') == 'PKR' ? 'selected' : '' }}>PKR – Pakistani Rupee</option>
                                <option value="USD" {{ old('currency', $customer->currency ?? '') == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                                <option value="AED" {{ old('currency', $customer->currency ?? '') == 'AED' ? 'selected' : '' }}>AED – UAE Dirham</option>
                            </select>
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($customer) ? 'Update Customer' : 'Save Customer' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
