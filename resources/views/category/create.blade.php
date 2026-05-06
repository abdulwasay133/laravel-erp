@extends('layouts.app')

@section('title', isset($category) ? 'Edit Category' : 'Add Category')
@section('page-title', isset($category) ? 'Edit Category' : 'Add Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('category.index') }}" class="text-decoration-none text-muted">Category</a></li>
    <li class="breadcrumb-item active">{{ isset($category) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

<div class="row justify-content-center">
    <div class="col-xl-9">

        <form action="{{ isset($category) ? route('category.update', $category->id) : route('category.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($category)) @method('PUT') @endif

            {{-- ── Basic Information ── --}}
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-vcard text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">
                            {{ isset($category) ? 'Edit Category' : 'Add Category' }}
                        </h6>
                        <p class="card-subtitle">Primary Category details</p>
                    </div>
                </div>
                <div class="card-body">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Category Name <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $category->name ?? '') }}"
                                   placeholder="e.g. Skin Care">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug"
                                   class="form-control @error('slug') is-invalid @enderror"
                                   value="{{ old('slug', $category->slug ?? '') }}"
                                   placeholder="e.g. sk">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Description <span class="text-danger">*</span></label>
                            <input type="text" name="description"
                                   class="form-control @error('description') is-invalid @enderror"
                                   value="{{ old('description', $category->description ?? '') }}"
                                   placeholder="e.g.  Lorem ipsum dolor sit amet">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="">-- Select Status --</option>
                                <option value="1" {{ old('status', $category->status ?? '') == '1' ? 'selected' : '' }} 
                                    {{ isset($category) && $category->status == 1 ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $category->status ?? '') == '0' ? 'selected' : '' }}
                                    {{ isset($category) && $category->status == 0 ? 'selected' : '' }}>Inactive</option>
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
                <a href="{{ route('category.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($category) ? 'Update Category' : 'Save Category' }}
                </button>
            </div>

        </form>
    </div>
</div>

@endsection