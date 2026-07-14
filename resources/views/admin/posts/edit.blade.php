@extends('layouts.admin')
@section('title', 'Edit post')
@section('content')
<x-page-header title="Edit post" />
<form method="POST" action="{{ route('admin.posts.update', $post) }}" enctype="multipart/form-data">@method('PUT')@include('admin.posts._form')</form>
@endsection
