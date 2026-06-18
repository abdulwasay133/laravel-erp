@extends('layouts.app')

@section('title', 'Add Expense')
@section('page-title', 'Add Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}" class="text-decoration-none text-muted">Expenses</a></li>
    <li class="breadcrumb-item active">Add</li>
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
        <form action="{{ route('expenses.store') }}" method="POST">
            @csrf
            @include('expenses.partials.form')
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#generateBtn').click(function () {
        $.get('{{ route('expenses.generate-voucher-no') }}', function (response) {
            $('#voucher_no').val(response.voucher_no);
        });
    });

    if ($('#voucher_no').val() === '') {
        $('#generateBtn').click();
    }

    function toggleBank() {
        const isBank = $('#paymentMethod').val() === 'bank';
        $('#bankAccountDiv').toggle(isBank);
    }

    $('#paymentMethod').on('change', toggleBank);
    toggleBank();
});
</script>
@endpush
