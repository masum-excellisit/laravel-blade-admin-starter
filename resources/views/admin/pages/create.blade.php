@extends('layouts.admin')
@section('title', 'New page')
@section('content')
<x-page-header title="New page" />
<form method="POST" action="{{ route('admin.pages.store') }}">@include('admin.pages._form')</form>
@endsection
