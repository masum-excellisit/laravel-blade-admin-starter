@extends('layouts.admin')
@section('title', 'Edit job')
@section('content')
<x-page-header title="Edit job listing" />
<form method="POST" action="{{ route('admin.jobs.update', $jobListing) }}">@method('PUT')@include('admin.jobs._form')</form>
@endsection
