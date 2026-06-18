@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Add User')
@section('page-title', isset($user) ? 'Edit User' : 'Add User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none text-muted">Users</a></li>
    <li class="breadcrumb-item active">{{ isset($user) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-8">

        <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}"
              method="POST">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-fill-add text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">{{ isset($user) ? 'Edit User' : 'Add User' }}</h6>
                        <p class="card-subtitle">User account details and access role</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-600">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name ?? '') }}"
                                   placeholder="John Doe">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email ?? '') }}"
                                   placeholder="john@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Role <span class="text-danger">*</span></label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="">-- Select Role --</option>
                                <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="manager" {{ old('role', $user->role ?? '') == 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="staff" {{ old('role', $user->role ?? '') == 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6"></div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">
                                Password @if(!isset($user))<span class="text-danger">*</span>@endif
                            </label>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   autocomplete="new-password"
                                   placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Password' }}">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">
                                Confirm Password @if(!isset($user))<span class="text-danger">*</span>@endif
                            </label>
                            <input type="password" name="password_confirmation"
                                   class="form-control"
                                   autocomplete="new-password"
                                   placeholder="Confirm password">
                        </div>

                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($user) ? 'Update User' : 'Save User' }}
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
