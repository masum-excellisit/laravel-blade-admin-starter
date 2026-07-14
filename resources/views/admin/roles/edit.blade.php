@extends('layouts.admin')
@section('title', 'Edit role')
@section('content')
<x-page-header title="Edit role: {{ $role->name }}" />
<x-card><form method="POST" action="{{ route('admin.roles.update', $role) }}">@method('PUT')@include('admin.roles._form')</form></x-card>
@endsection
