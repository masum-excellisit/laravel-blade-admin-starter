@extends('layouts.admin')
@section('title', 'Edit service')
@section('content')
<x-page-header title="Edit service" />
<form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">@method('PUT')@include('admin.services._form')</form>
@endsection
