@extends('layouts.app')

@section('title', 'Near to Expiry Print')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4>Near to Expiry Products</h4>
            <div>{{ now()->format('d M Y') }}</div>
        </div>
        <div>
            <button class="btn btn-outline-secondary" onclick="window.print()">Print</button>
        </div>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>S.no</th>
                <th>Product Name</th>
                <th>Batch No</th>
                <th class="text-center">Qty Left</th>
                <th>Expiry Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batches as $index => $batch)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $batch->product_name }}</td>
                    <td>{{ $batch->batch_number }}</td>
                    <td class="text-center">{{ $batch->quantity }}</td>
                    <td>{{ \Carbon\Carbon::parse($batch->expiry_date)->format('d M Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No near to expiry products found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
