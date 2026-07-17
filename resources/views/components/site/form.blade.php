@props(['slug'])
@php
    $embeddedForm = \App\Models\Form::query()
        ->where('slug', $slug)
        ->where('is_active', true)
        ->with('fields')
        ->first();
@endphp

@if($embeddedForm)
    @include('site.forms._form', ['form' => $embeddedForm])
@endif
