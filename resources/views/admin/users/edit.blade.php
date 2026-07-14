@extends('layouts.admin')
@section('title', 'Edit admin user')
@section('content')
<x-page-header title="Edit admin user" />
<x-card class="max-w-3xl">
    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">@method('PUT')@include('admin.users._form')</form>
</x-card>
@endsection
