@extends('layouts.app')
@section('title', 'Edit Vendor')
@section('page-title', 'Edit Vendor')
@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendors.update', $vendor) }}" method="POST">@csrf @method('PUT')
                @include('admin.vendors.form', ['vendor' => $vendor])</form>
        </div>
    </div>
@endsection
