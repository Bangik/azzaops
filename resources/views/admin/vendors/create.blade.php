@extends('layouts.app')
@section('title', 'Tambah Vendor')
@section('page-title', 'Tambah Vendor')
@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendors.store') }}" method="POST">@csrf @include('admin.vendors.form', ['vendor' => null])</form>
        </div>
    </div>
@endsection
