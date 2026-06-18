@extends('layouts.app')

@section('title', 'Add Employee')
@section('page-title', 'Add Employee')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}" class="text-decoration-none text-muted">Employees</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <form action="{{ route('employees.store') }}" method="POST">
            @csrf
            @include('employees.partials.form')
        </form>
    </div>
</div>
@endsection
