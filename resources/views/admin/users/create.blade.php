@extends('layouts.admin')
@section('title', 'New admin user')
@section('content')
<x-page-header title="New admin user" />
<x-card class="max-w-3xl">
    <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">@include('admin.users._form')</form>
</x-card>
@endsection
