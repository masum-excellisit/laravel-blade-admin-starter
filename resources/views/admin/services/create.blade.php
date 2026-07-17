@extends('layouts.admin')
@section('title', 'New service')
@section('content')
<x-page-header title="New service" />
<form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">@include('admin.services._form')</form>
@endsection
