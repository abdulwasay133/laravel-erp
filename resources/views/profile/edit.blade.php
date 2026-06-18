@extends('layouts.app')

@section('title', 'Profile')
@section('page-title', 'My Profile')
@section('breadcrumb')
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')

{{-- Update Profile Information --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-person-circle text-primary-custom"></i>
        <div>
            <h6 class="card-title">Profile Information</h6>
            <p class="card-subtitle">Update your account's profile information and email address.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('profile.update') }}" class="row g-3">
            @csrf
            @method('patch')

            <div class="col-md-6">
                <label class="form-label fw-600">Name</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-600">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
                @if (session('status') === 'profile-updated')
                    <span class="text-success align-self-center fw-500">Saved.</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Update Password --}}
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-shield-lock text-primary-custom"></i>
        <div>
            <h6 class="card-title">Update Password</h6>
            <p class="card-subtitle">Ensure your account is using a long, random password to stay secure.</p>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('password.update') }}" class="row g-3">
            @csrf
            @method('put')

            <div class="col-md-4">
                <label class="form-label fw-600">Current Password</label>
                <input type="password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                       autocomplete="current-password">
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600">New Password</label>
                <input type="password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                       autocomplete="new-password">
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control"
                       autocomplete="new-password">
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save
                </button>
                @if (session('status') === 'password-updated')
                    <span class="text-success align-self-center fw-500">Saved.</span>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Delete Account --}}
<div class="card mb-4 border-danger">
    <div class="card-header">
        <i class="bi bi-exclamation-triangle text-danger"></i>
        <div>
            <h6 class="card-title text-danger">Delete Account</h6>
            <p class="card-subtitle">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
        </div>
    </div>
    <div class="card-body">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            <i class="bi bi-trash me-1"></i> Delete Account
        </button>
    </div>
</div>

{{-- Delete Account Confirmation Modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')
                <div class="modal-header">
                    <h6 class="modal-title">Are you sure?</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm.</p>
                    <div class="mt-3">
                        <label class="form-label fw-600">Password</label>
                        <input type="password" name="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                               placeholder="Your password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if($errors->userDeletion->isNotEmpty())
        document.addEventListener('DOMContentLoaded', function () {
            const modal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
            modal.show();
        });
    @endif
</script>
@endpush

@endsection
