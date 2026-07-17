@extends('layouts.admin')
@section('title', 'Edit form')
@section('content')
<x-page-header title="Edit form">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.forms.submissions.index', $form)">Submissions</x-btn>
    </x-slot:actions>
</x-page-header>
<form method="POST" action="{{ route('admin.forms.update', $form) }}">@method('PUT')@include('admin.forms._form')</form>
@endsection
