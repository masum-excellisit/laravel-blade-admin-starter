@extends('layouts.app')
@section('title', $form->name)
@section('content')
<div class="brand-gradient text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-20 text-center">
        <h1 class="text-4xl sm:text-5xl font-bold">{{ $form->name }}</h1>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-16">
    <div class="rounded-2xl border border-slate-100 shadow-sm p-8">
        @include('site.forms._form', ['form' => $form])
    </div>
</div>
@endsection
