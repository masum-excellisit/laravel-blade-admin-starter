@extends('layouts.admin')
@section('title', 'Edit portfolio item')
@section('content')
<x-page-header title="Edit portfolio item" />
<form method="POST" action="{{ route('admin.portfolio.update', $portfolioItem) }}" enctype="multipart/form-data">@method('PUT')@include('admin.portfolio._form')</form>
@endsection
