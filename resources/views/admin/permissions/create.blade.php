@extends('layouts.admin')
@section('title', 'New permission')
@section('content')
<x-page-header title="New permission" subtitle="Use dot notation, e.g. faqs.view" />
<x-card class="max-w-md"><form method="POST" action="{{ route('admin.permissions.store') }}" class="space-y-5">@csrf
    <x-form.input name="name" label="Permission name" required placeholder="module.action" />
    <x-btn type="submit">Create</x-btn>
</form></x-card>
@endsection
