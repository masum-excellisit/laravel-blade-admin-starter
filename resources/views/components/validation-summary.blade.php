@if($errors->any())
@php $count = $errors->count(); @endphp
<div class="mb-5 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950/40 dark:border-red-900/50 px-4 py-3 text-sm text-red-700 dark:text-red-200" role="alert">
    <p class="font-semibold">{{ $count }} {{ \Illuminate\Support\Str::plural('field', $count) }} need attention.</p>
    <ul class="mt-2 list-disc list-inside space-y-0.5 text-red-600/90 dark:text-red-300/90">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
