@extends('layouts.app')

@section('title', 'Chart of Account Details')
@section('page-title', 'Chart of Account Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('chart-of-accounts.index') }}">Chart of Accounts</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">Account Details</h6>
                <p class="card-subtitle">View account information</p>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Account Code</label>
                        <div class="fw-semibold">{{ $account->code }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Account Name</label>
                        <div class="fw-semibold">{{ $account->name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Account Type</label>
                        <div class="fw-semibold">{{ $account->type_label }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Subtype</label>
                        <div class="fw-semibold">{{ $account->subtype ? $account->subtype_label : '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Parent Account</label>
                        <div class="fw-semibold">{{ $account->parent ? $account->parent->code . ' - ' . $account->parent->name : 'None (Root Account)' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Level</label>
                        <div class="fw-semibold">{{ $account->level }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Opening Balance</label>
                        <div class="fw-semibold">Rs. {{ number_format($account->opening_balance, 2, '.', ',') }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Current Balance</label>
                        <div class="fw-semibold text-success">Rs. {{ number_format($account->current_balance, 2, '.', ',') }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Status</label>
                        <div class="fw-semibold">
                            @if($account->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="text-muted small">Description</label>
                        <div class="fw-semibold">{{ $account->description ?: 'No description provided' }}</div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                    <div>
                        <a href="{{ route('chart-of-accounts.edit', $account->id) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($account->children->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="card-title">Child Accounts</h6>
                <p class="card-subtitle">Sub-accounts under this account</p>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($account->children as $child)
                            <tr>
                                <td>{{ $child->code }}</td>
                                <td>{{ $child->name }}</td>
                                <td>{{ $child->type_label }}</td>
                                <td class="text-end">Rs. {{ number_format($child->current_balance, 2, '.', ',') }}</td>
                                <td>
                                    @if($child->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('chart-of-accounts.show', $child->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
