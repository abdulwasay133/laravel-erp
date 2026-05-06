@extends('layouts.app')

@section('title', isset($unit) ? 'Edit Unit' : 'Add Unit')
@section('page-title', isset($unit) ? 'Edit Unit' : 'Add Unit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('unit.unitlist') }}" class="text-decoration-none text-muted">Unit</a></li>
    <li class="breadcrumb-item active">{{ isset($unit) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-9">

        <form action="{{ isset($unit) ? route('unit.update', $unit->id) : route('unit.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($unit)) @method('PUT') @endif

            {{-- ── Basic Information ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-vcard text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">
                            {{ isset($unit) ? 'Edit Unit' : 'Add Unit' }}
                        </h6>
                        <p class="card-subtitle">Primary Unit details</p>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Unit Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $unit->name ?? '') }}"
                                   placeholder="e.g. kilogram">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Short Name <span class="text-danger">*</span></label>
                            <input type="text" name="symbol"
                                   class="form-control @error('symbol') is-invalid @enderror"
                                   value="{{ old('symbol', $unit->symbol ?? '') }}"
                                   placeholder="e.g. kg">
                            @error('symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Type <span class="text-danger">*</span></label>
                            <input type="text" name="type"
                                   class="form-control @error('type') is-invalid @enderror"
                                   value="{{ old('type', $unit->type ?? '') }}"
                                   placeholder="e.g. weight,mass,gass">
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="">-- Select Status --</option>
                                <option value="1" {{ old('status', $unit->status ?? '') == '1' ? 'selected' : '' }} 
                                    {{ isset($unit) && $unit->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $unit->status ?? '') == '0' ? 'selected' : '' }}
                                    {{ isset($unit) && $unit->status == 0 ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                </div>
            </div>


            {{-- ── Buttons ── --}}
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('unit.unitlist') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($unit) ? 'Update Unit' : 'Save Unit' }}
                </button>
            </div>

        </form>
    </div>
</div>

@endsection