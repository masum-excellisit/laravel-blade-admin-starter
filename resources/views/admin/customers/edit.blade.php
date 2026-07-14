@extends('layouts.admin')
@section('title', 'Edit user')
@section('content')
<x-page-header title="Edit user" />
<x-card class="max-w-3xl">
    <form method="POST" action="{{ route('admin.customers.update', $customer) }}" enctype="multipart/form-data">@method('PUT')@include('admin.customers._form')</form>
</x-card>
@endsection
