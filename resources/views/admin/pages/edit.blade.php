@extends('layouts.admin')
@section('title', 'Edit page')
@section('content')
<x-page-header title="Edit page" />
<form method="POST" action="{{ route('admin.pages.update', $page) }}">@method('PUT')@include('admin.pages._form')</form>
@endsection
