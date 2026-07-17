@extends('layouts.admin')
@section('title', 'New portfolio item')
@section('content')
<x-page-header title="New portfolio item" />
<form method="POST" action="{{ route('admin.portfolio.store') }}" enctype="multipart/form-data">@include('admin.portfolio._form')</form>
@endsection
