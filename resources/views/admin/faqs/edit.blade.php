@extends('layouts.admin')
@section('title', 'Edit FAQ')
@section('content')
<x-page-header title="Edit FAQ" />
<form method="POST" action="{{ route('admin.faqs.update', $faq) }}">@method('PUT')@include('admin.faqs._form')</form>
@endsection
