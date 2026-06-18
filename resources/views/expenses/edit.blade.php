@extends('layouts.app')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-decoration-none text-muted">Expenses</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('expenses.update', $expense->id) }}" method="POST">
            @csrf
            @method('PUT')
            @include('expenses.partials.form', ['expense' => $expense])
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function toggleBank() {
        const isBank = $('#paymentMethod').val() === 'bank';
        $('#bankAccountDiv').toggle(isBank);
    }

    $('#paymentMethod').on('change', toggleBank);
    toggleBank();
});
</script>
@endpush
