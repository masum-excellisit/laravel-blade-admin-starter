@extends('layouts.admin')
@section('title', 'New testimonial')
@section('content')
<x-page-header title="New testimonial" />
<form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data">@include('admin.testimonials._form')</form>
@endsection
