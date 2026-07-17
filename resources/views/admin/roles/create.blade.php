@extends('layouts.admin')
@section('title', 'New role')
@section('content')
<x-page-header title="New role" subtitle="Create a role and assign module permissions." />
<form method="POST" action="{{ route('admin.roles.store') }}">
    @php($assigned=[])
    @include('admin.roles._form')
</form>
@endsection
