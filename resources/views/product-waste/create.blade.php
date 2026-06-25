@extends('layouts.app')

@section('title', 'New Waste Record')
@section('page-title', 'Product Waste')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('product-waste.index') }}" class="text-decoration-none text-muted">Product Waste</a></li>
    <li class="breadcrumb-item active">New Waste</li>
@endsection

@section('content')
<form method="POST" action="{{ route('product-waste.store') }}" id="wasteForm">
    @csrf

    <div class="card mb-3">
        <div class="card-header">
            <h6 class="card-title mb-0">Waste Details</h6>
            <p class="card-subtitle mb-0">Record product waste / wastage</p>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product <span class="text-danger">*</span></label>
                    <select name="product_id" id="productSelect" class="form-select" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Stock: {{ $product->quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Batch <span class="text-danger">*</span></label>
                    <select name="product_batch_id" id="batchSelect" class="form-select" required>
                        <option value="">Select product first</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" class="form-control" min="1" value="{{ old('quantity') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Waste Date <span class="text-danger">*</span></label>
                    <input type="date" name="waste_date" class="form-control" value="{{ old('waste_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control" placeholder="Reason for waste" value="{{ old('reason') }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger">
            <i class="bi bi-trash3 me-1"></i> Record Waste
        </button>
        <a href="{{ route('product-waste.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    $('#productSelect').on('change', function () {
        const productId = $(this).val();
        const $batch = $('#batchSelect').html('<option value="">Loading...</option>');

        if (!productId) {
            $batch.html('<option value="">Select product first</option>');
            return;
        }

        $.get('{{ route("product-waste.batches") }}', { product_id: productId })
            .done(function (batches) {
                if (batches.length === 0) {
                    $batch.html('<option value="">No batches with stock</option>');
                    return;
                }
                let html = '<option value="">Select batch</option>';
                batches.forEach(function (b) {
                    const expiry = b.expiry_date ? ' (Expires: ' + b.expiry_date + ')' : '';
                    html += '<option value="' + b.id + '" data-cost="' + b.cost + '" data-qty="' + b.quantity + '">' +
                        b.batch_number + ' - Qty: ' + b.quantity + ' - Cost: Rs. ' + parseFloat(b.cost).toFixed(2) + expiry +
                        '</option>';
                });
                $batch.html(html);
            })
            .fail(function () {
                $batch.html('<option value="">Error loading batches</option>');
            });
    });

    @if(old('product_id'))
        $('#productSelect').trigger('change');
    @endif
});
</script>
@endpush
