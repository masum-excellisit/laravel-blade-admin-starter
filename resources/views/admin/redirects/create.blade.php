@extends('layouts.admin')
@section('title', 'New redirect')
@section('content')
<x-page-header title="New redirect" />
<form method="POST" action="{{ route('admin.redirects.store') }}">@include('admin.redirects._form')</form>
@endsection
