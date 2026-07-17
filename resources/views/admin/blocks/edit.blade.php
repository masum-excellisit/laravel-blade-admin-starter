@extends('layouts.admin')
@section('title', 'Edit content block')
@section('content')
<x-page-header title="Edit content block" />
<form method="POST" action="{{ route('admin.blocks.update', $block) }}">@method('PUT')@include('admin.blocks._form')</form>
@endsection
