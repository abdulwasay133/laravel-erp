@extends('layouts.app')

@section('title', isset($product) ? 'Edit Product' : 'Add Product')
@section('page-title', isset($product) ? 'Edit Product' : 'Add Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product.index') }}" class="text-decoration-none text-muted">Product</a></li>
    <li class="breadcrumb-item active">{{ isset($product) ? 'Edit' : 'Create' }}</li>
@endsection
@section('content')




<form method="POST" action="{{ isset($product) ? route('product.update', $product) : route('product.store') }}" id="productForm">
    @csrf
    @if(isset($product)) @method('PUT') @endif

    <div class="form-layout">

        {{-- LEFT COLUMN --}}
        <div class="form-layout__main">

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
                            <label class="form-label fw-600">Product Name <span class="text-danger">*</span></label>
                            <input type="text" name="product_name"
                                   class="form-control @error('product_name') is-invalid @enderror"
                                   value="{{ old('product_name', $product->name ?? '') }}"
                                   placeholder="e.g. Coke 500ml">
                            @error('product_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-md-6">
                            <label class="form-label fw-600"> SKU/Bar Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="sku" id="skuField"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       value="{{ old('sku', $product->sku ?? '') }}"
                                       placeholder="Auto Generated">
                                       <span  onclick="generateSKU()" type="button" class="input-group-text"><i class="bi bi-arrow-clockwise"></i></span>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                         <div class="col-md-6">
                            <label class="form-label fw-600">Units <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select @error('unit') is-invalid @enderror">
                                <option value='0' selected disabled >Select Unit </option>
                                @foreach( $units as $unit)
                                    <option value="{{ $unit->id }}" 
                                        {{ old('unit', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <div class="col-md-6">
                            <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror">
                                <option value='0' selected disabled >Select Category </option>
                                @foreach( $categories as $category)
                                    <option value="{{ $category->id }}" 
                                        {{ old('category', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-600">Notes</label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Any additional notes...">{{ old('notes', $product->description ?? '') }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Stock &amp; Pricessing</h6>
                        <p class="card-subtitle">Stock / Pricessing</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        
                        <div class="col-md-3">
                            <label class="form-label fw-600">Opening Stock</label>
                            <input type="number" name="opening_stock" class="form-control"
                                   value="{{ old('opening_stock', $product->batches[0]->quantity ?? '') }}"
                                   placeholder="0">
                                   @error('opening_stock')
                                       <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600">Sale Price</label>
                                <input type="number" name="sale_price" class="form-control  @error('sale_price') is-invalid @enderror""
                                       value="{{ old('sale_price', $product->price ?? '') }}"
                                       placeholder="0">
                            @error('sale_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="col-md-3">
                            <label class="form-label fw-600"> Purchase Price</label>
                            <input type="number" name="purchase_price" class="form-control  @error('purchase_price') is-invalid @enderror""
                                   value="{{ old('purchase_price', $product->batches[0]->cost ?? '') }}"
                                   placeholder="0">

                                   @error('purchase_price')
                                       <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-600"> Minimum Quantity</label>
                            <input type="number" name="minimum_quantity" class="form-control @error('minimum_quantity') is-invalid @enderror"
                                   value="{{ old('minimum_quantity', $product->alert_quantity ?? '') }}"
                                   placeholder="10">
                                   @error('minimum_quantity')
                                       <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Expiry Management</h6>
                        <p class="card-subtitle">Stock / Expiry</p>
                    </div>
                </div>
                <div class="card-body">
                    <label class="toggle-switch">
                        <input type="checkbox" name="has_expiry" class="p-2" id="hasExpiry" value="1"
                               {{ old('has_expiry', $product->is_expiry ?? false) ? 'checked' : '' }}
                               onchange="toggleExpiry(this)">
                        <span class="toggle-switch__slider"></span>
                        <span class="toggle-switch__label">Track Expiry</span>
                    </label>
                    <div class="row g-3" id="expiryFields" style="{{ old('has_expiry', $product->has_expiry ?? false) ? '' : '' }}">
                        {{-- <div class="col-12">
                            <label class="form-label fw-600">Street Address</label>
                            <input type="text" name="address" class="form-control"
                                   value="{{ old('address', $product->address ?? '') }}"
                                   placeholder="Street, Block, Area">
                        </div> --}}
                        <div class="col-md-4">
                            <label class="form-label fw-600">Expiry Date</label>
                            <input type="date" name="expiry_date" class="form-control"
                                   value="{{ old('expiry_date', $product->batches[0]->expiry_date ?? '') }}"
                                   placeholder="0">
                                   @error('expiry_date')
                                       <div class="invalid-feedback">{{ $message }}</div>                                       
                                   @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Batch / Lot Number</label>
                                <input type="text" name="batch_number" class="form-control"
                                       value="{{ old('batch_number', $product->batches[0]->batch_number ?? '') }}"
                                       placeholder="0">
                            @error('batch_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                              
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600"> Expiry Alert (days before)</label>
                            <input type="text" name="expriy_alert_days" class="form-control"
                                   value="{{ old('expriy_alert_days', $product->alert_days ?? '') }}"
                                   placeholder="30">
                                   @error('expriy_alert_days')
                                       <div class="invalid-feedback">{{ $message }}</div>
                                   @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock & Pricing --}}


            {{-- Expiry Management --}}

                        <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-geo-alt text-primary-custom"></i>
                    <div>
                        <h6 class="card-title">Suppliers &amp; Pricing</h6>
                        <p class="card-subtitle">Suppliers / Vender Information</p>
                    </div>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary btn-sm" onclick="addSupplierRow()">
                        + Add Supplier
                    </button>
                <div class="card_body p-0">
                    <table class="data-table supplier-table w-100" id="supplierTable">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Unit Cost (PKR)</th>
                                <th>Preferred</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="supplierRows">
                            @php $existingSuppliers = old('suppliers', isset($product) ? $product->suppliers->map(fn($s) => [
                                'supplier_id' => $s->id,
                                'unit_price'  => $s->pivot->cost,
                                'min_order_qty'=> $s->pivot->min_order_qty,
                                'lead_time_days'=> $s->pivot->lead_time_days,
                                'is_preferred' => $s->pivot->is_preferred,
                            ])->toArray() : []); @endphp

                            @forelse($existingSuppliers as $i => $sup)
                            <tr class="supplier-row" data-index="{{ $i }}">
                                <td>
                                    <select name="suppliers[{{ $i }}][supplier_id]" class="form-control form-control--sm" required>
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}" {{ ($sup['supplier_id'] ?? '') == $s->id ? 'selected' : '' }}>{{ $s->first_name . ' ' . $s->last_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="suppliers[{{ $i }}][unit_price]" class="form-control form-control--sm supplier-price"
                                           value="{{ $sup['unit_price'] ?? '' }}" min="0" step="0.01" placeholder="0.00" required onchange="updateMargin({{ $i }})">
                                </td>
                                
                                
                                <td class="text-center">
                                    <input type="radio" name="preferred_supplier" value="{{ $i }}"
                                           {{ ($sup['is_preferred'] ?? false) ? 'checked' : '' }}>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSupplierRow(this)">✕</button>
                                </td>
                            </tr>
                            @empty
                            <tr id="noSuppliersRow">
                                <td colspan="6" class="empty-state">
                                    <div class="empty-state__inner empty-state__inner--sm">
                                        <span>No suppliers added yet.</span>
                                        <button type="button" class="btn btn-secondary btn-sm ml-2" onclick="addSupplierRow()">Add one</button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </div>
            </div>


            {{-- Supplier Pricing --}}


        </div>

        {{-- RIGHT COLUMN --}}
        <div class="form-layout__sidebar">

            {{-- Status --}}
            <div class="card p-3 mt-2">
                <div class="card__header"><h2 class="card__title">Status</h2></div>
                <div class="card__body">
                    <label class="toggle-switch">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                        <span class="toggle-switch__slider"></span>
                        <span class="toggle-switch__label">Product is Active</span>
                    </label>
                </div>
            </div>



            {{-- ── Buttons ── --}}
            <div class="d-flex gap-2 justify-content-end mt-3">
                <a href="{{ route('product.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($product) ? 'Update Product' : 'Save Product' }}
                </button>
            </div>

        </div>
    </div>
</form>

@push('scripts')
<script>
let supplierIndex = {{ count($existingSuppliers ?? []) }};

// ── Expiry toggle ────────────────────────────────────────────────────────────
function toggleExpiry(cb) {
    document.getElementById('expiryFields').style.display = cb.checked ? '' : 'none';
}

// Live expiry status bar
document.getElementById('expiryDateField')?.addEventListener('change', function() {
    const bar = document.getElementById('expiryStatusBar');
    const val = this.value;
    if (!val) { bar.style.display = 'none'; return; }
    const days = Math.ceil((new Date(val) - new Date()) / 86400000);
    bar.style.display = '';
    if (days < 0) {
        bar.className = 'expiry-status-bar expiry-status-bar--expired';
        bar.innerHTML = `⚠️ This product has already expired ${Math.abs(days)} day(s) ago.`;
    } else if (days <= 30) {
        bar.className = 'expiry-status-bar expiry-status-bar--soon';
        bar.innerHTML = `🕐 Expiring in <strong>${days}</strong> day(s). Consider replenishment.`;
    } else {
        bar.className = 'expiry-status-bar expiry-status-bar--ok';
        bar.innerHTML = `✅ Valid for <strong>${days}</strong> more day(s).`;
    }
});

// ── SKU generator ────────────────────────────────────────────────────────────
function generateSKU() {
    const prefix = 'PRD';
    const rand = Math.random().toString(36).substring(2, 7).toUpperCase();
    document.getElementById('skuField').value = `${prefix}-${rand}`;
}

// ── Supplier rows ────────────────────────────────────────────────────────────
function addSupplierRow() {
    document.getElementById('noSuppliersRow')?.remove();
    const i = supplierIndex++;
    const row = document.createElement('tr');
    row.className = 'supplier-row';
    row.dataset.index = i;
    row.innerHTML = `
        <td>
            <select name="suppliers[${i}][supplier_id]" class="form-control form-control--sm" required>
                <option value="">Select Supplier</option>
                @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->first_name . ' ' . $s->last_name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="suppliers[${i}][unit_price]" class="form-control form-control--sm supplier-price" min="0" step="0.01" placeholder="0.00" required onchange="updateMargin(${i})"></td>

        <td class="text-center"><input type="radio" name="preferred_supplier" value="${i}"></td>
        <td><button type="button" class="action-btn action-btn--danger" onclick="removeSupplierRow(this)">✕</button></td>
    `;
    document.getElementById('supplierRows').appendChild(row);
    updateMarginPreview();
}

function removeSupplierRow(btn) {
    btn.closest('tr').remove();
    updateMarginPreview();
    if (!document.querySelectorAll('.supplier-row').length) {
        const empty = `<tr id="noSuppliersRow"><td colspan="6" class="empty-state"><div class="empty-state__inner empty-state__inner--sm"><span>No suppliers added yet.</span><button type="button" class="btn btn--ghost btn--sm ml-2" onclick="addSupplierRow()">Add one</button></div></td></tr>`;
        document.getElementById('supplierRows').innerHTML = empty;
    }
}

// ── Margin preview ────────────────────────────────────────────────────────────


document.querySelector('[name="sale_price"]')?.addEventListener('input', updateMarginPreview);
updateMarginPreview();
</script>
@endpush
@endsection