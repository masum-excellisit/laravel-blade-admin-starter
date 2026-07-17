@extends('layouts.admin')
@section('title', 'Edit role')
@section('content')
<x-page-header title="Edit role: {{ $role->name }}" subtitle="Update this role and its module permissions." />
<form method="POST" action="{{ route('admin.roles.update', $role) }}">
    @method('PUT')
    @include('admin.roles._form')
</form>
@endsection
