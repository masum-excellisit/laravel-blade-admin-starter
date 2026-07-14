@extends('layouts.admin')
@section('title', 'Edit permission')
@section('content')
<x-page-header title="Edit permission" />
<x-card class="max-w-md"><form method="POST" action="{{ route('admin.permissions.update', $permission) }}" class="space-y-5">@csrf @method('PUT')
    <x-form.input name="name" label="Permission name" :value="$permission->name" required />
    <x-btn type="submit">Update</x-btn>
</form></x-card>
@endsection
