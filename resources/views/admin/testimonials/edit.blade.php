@extends('layouts.admin')
@section('title', 'Edit testimonial')
@section('content')
<x-page-header title="Edit testimonial" />
<form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data">@method('PUT')@include('admin.testimonials._form')</form>
@endsection
