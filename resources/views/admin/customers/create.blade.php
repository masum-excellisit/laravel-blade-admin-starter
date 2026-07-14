@extends('layouts.admin')
@section('title', 'New user')
@section('content')
<x-page-header title="New user" />
<x-card class="max-w-3xl">
    <form method="POST" action="{{ route('admin.customers.store') }}" enctype="multipart/form-data">@include('admin.customers._form')</form>
</x-card>
@endsection
