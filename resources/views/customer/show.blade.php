@extends('layouts.app')

@section('title', 'Customer Details')
@section('page-title', 'Customer Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none text-muted">Customers</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header justify-content-between align-items-center">
                <div>
                    <h6 class="card-title">Customer Overview</h6>
                    <p class="card-subtitle">Detailed customer profile</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center"
                         style="width:70px;height:70px;font-size:24px;">
                        {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="mb-0">{{ $customer->first_name }} {{ $customer->last_name }}</h4>
                        <small class="text-muted">{{ $customer->company ?? 'Individual customer' }}</small>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <div class="fw-semibold">{{ $customer->email }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <div class="fw-semibold">{{ $customer->phone }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Type</label>
                        <div class="fw-semibold text-capitalize">{{ $customer->type }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Status</label>
                        <div class="fw-semibold text-capitalize">{{ $customer->status }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">City</label>
                        <div class="fw-semibold">{{ $customer->city ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Postal Code</label>
                        <div class="fw-semibold">{{ $customer->postal_code ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Province</label>
                        <div class="fw-semibold">{{ $customer->province ?? '—' }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Balance</label>
                        <div class="fw-bold">Rs. {{ number_format($customer->balance ?? 0) }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small">Address</label>
                    <div class="fw-semibold">{{ $customer->address ?? 'No address provided' }}</div>
                </div>

                <div>
                    <label class="text-muted small">Notes</label>
                    <div class="fw-semibold">{{ $customer->notes ?? 'No notes available' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
