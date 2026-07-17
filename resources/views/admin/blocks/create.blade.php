@extends('layouts.admin')
@section('title', 'New content block')
@section('content')
<x-page-header title="New content block" />
<form method="POST" action="{{ route('admin.blocks.store') }}">@include('admin.blocks._form')</form>
@endsection
