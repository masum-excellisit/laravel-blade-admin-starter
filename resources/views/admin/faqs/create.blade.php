@extends('layouts.admin')
@section('title', 'New FAQ')
@section('content')
<x-page-header title="New FAQ" />
<form method="POST" action="{{ route('admin.faqs.store') }}">@include('admin.faqs._form')</form>
@endsection
