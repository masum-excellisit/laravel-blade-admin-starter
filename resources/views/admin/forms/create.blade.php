@extends('layouts.admin')
@section('title', 'New form')
@section('content')
<x-page-header title="New form" />
<form method="POST" action="{{ route('admin.forms.store') }}">@include('admin.forms._form')</form>
@endsection
