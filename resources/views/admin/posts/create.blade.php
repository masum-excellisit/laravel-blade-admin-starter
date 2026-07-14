@extends('layouts.admin')
@section('title', 'New post')
@section('content')
<x-page-header title="New post" />
<form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data">@include('admin.posts._form')</form>
@endsection
