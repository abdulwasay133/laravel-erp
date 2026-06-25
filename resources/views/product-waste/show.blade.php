@extends('layouts.app')

@section('title', 'Waste Details')
@section('page-title', 'Waste Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-waste.index') }}" class="text-decoration-none text-muted">Product Waste</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="card-title">Waste Record #{{ $waste->id }}</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <small class="text-muted">Product</small>
                <div class="fw-semibold">{{ $waste->product?->name ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Batch Number</small>
                <div class="fw-semibold">{{ $waste->batch_number ?? '-' }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Quantity</small>
                <div class="fw-semibold">{{ $waste->quantity }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Unit Cost</small>
                <div class="fw-semibold">Rs. {{ number_format($waste->unit_cost, 2) }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Total Cost</small>
                <div class="fw-semibold">Rs. {{ number_format($waste->total_cost, 2) }}</div>
            </div>
            <div class="col-md-4">
                <small class="text-muted">Waste Date</small>
                <div class="fw-semibold">{{ $waste->waste_date->format('d M Y') }}</div>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Reason</small>
                <div class="fw-semibold">{{ $waste->reason ?? '-' }}</div>
            </div>
            <div class="col-md-6">
                <small class="text-muted">Recorded By</small>
                <div class="fw-semibold">{{ $waste->createdBy?->name ?? '-' }}</div>
            </div>
            @if($waste->notes)
            <div class="col-12">
                <small class="text-muted">Notes</small>
                <div class="fw-semibold">{{ $waste->notes }}</div>
            </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <a href="{{ route('product-waste.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>
</div>
@endsection
