@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Company Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-building text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Company Information</h6>
                        <p class="card-subtitle">Update your business details</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" required
                                   value="{{ old('company_name', $settings['company_name'] ?? '') }}" placeholder="Your Company">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Company Email</label>
                            <input type="email" name="company_email" class="form-control"
                                   value="{{ old('company_email', $settings['company_email'] ?? '') }}" placeholder="info@company.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Phone</label>
                            <input type="text" name="company_phone" class="form-control"
                                   value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" placeholder="+92 300 1234567">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Website</label>
                            <input type="url" name="company_website" class="form-control"
                                   value="{{ old('company_website', $settings['company_website'] ?? '') }}" placeholder="https://example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Address</label>
                            <textarea name="company_address" class="form-control" rows="2"
                                      placeholder="Street, City, Country...">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-image text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Company Logo</h6>
                        <p class="card-subtitle">Upload your logo (JPEG, PNG, WebP — max 2MB)</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-4 text-center">
                            @if(!empty($settings['company_logo']))
                                <img src="{{ asset('storage/' . $settings['company_logo']) }}"
                                     class="img-thumbnail mb-2" style="max-height: 120px;" alt="Logo">
                            @else
                                <div class="border rounded d-flex align-items-center justify-content-center bg-light"
                                     style="height: 120px;">
                                    <i class="bi bi-image text-muted fs-1"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <input type="file" name="company_logo" class="form-control" accept="image/*">
                            <div class="form-text">Leave empty to keep current logo.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-file-text text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Invoice Settings</h6>
                        <p class="card-subtitle">Terms & conditions for print documents</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-600">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="3"
                                      placeholder="Payment is due within 30 days...">{{ old('terms_conditions', $settings['terms_conditions'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
