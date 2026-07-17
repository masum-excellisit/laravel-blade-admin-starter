@extends('layouts.admin')
@section('title', 'Edit redirect')
@section('content')
<x-page-header title="Edit redirect" />
<form method="POST" action="{{ route('admin.redirects.update', $redirect) }}">@method('PUT')@include('admin.redirects._form')</form>
@endsection
