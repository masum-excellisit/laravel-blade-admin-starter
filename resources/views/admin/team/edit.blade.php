@extends('layouts.admin')
@section('title', 'Edit team member')
@section('content')
<x-page-header title="Edit team member" />
<form method="POST" action="{{ route('admin.team.update', $teamMember) }}" enctype="multipart/form-data">@method('PUT')@include('admin.team._form')</form>
@endsection
