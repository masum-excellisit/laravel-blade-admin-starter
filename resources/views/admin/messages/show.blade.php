@extends('layouts.admin')
@section('title', 'Message')
@section('content')
<x-page-header title="{{ $message->subject ?? 'Message' }}" :subtitle="'From '.$message->name.' <'.$message->email.'>'">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.messages.index')">Back</x-btn>
        @can('messages.delete')<form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<x-btn variant="danger" type="submit">Delete</x-btn></form>@endcan
    </x-slot:actions>
</x-page-header>
<x-card class="max-w-2xl">
    <p class="text-sm text-slate-400 mb-4">{{ $message->created_at->format('F j, Y g:i a') }}</p>
    <p class="whitespace-pre-line text-slate-700 dark:text-slate-200">{{ $message->message }}</p>
    <div class="mt-6"><x-btn :href="'mailto:'.$message->email">Reply by email</x-btn></div>
</x-card>
@endsection
