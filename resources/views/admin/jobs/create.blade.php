@extends('layouts.admin')
@section('title', 'New job')
@section('content')
<x-page-header title="New job listing" />
<form method="POST" action="{{ route('admin.jobs.store') }}">@include('admin.jobs._form')</form>
@endsection
