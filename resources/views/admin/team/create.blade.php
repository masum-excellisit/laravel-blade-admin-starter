@extends('layouts.admin')
@section('title', 'New team member')
@section('content')
<x-page-header title="New team member" />
<form method="POST" action="{{ route('admin.team.store') }}" enctype="multipart/form-data">@include('admin.team._form')</form>
@endsection
