@extends('layouts.admin')
@section('title', 'Edit '.$pageTitle)
@section('content')
<x-page-header :title="$pageTitle" subtitle="CMS page sections and fields.">
    <x-slot:actions>
        <x-btn variant="outline" :href="route('admin.cms.index')">Back to pages</x-btn>
    </x-slot:actions>
</x-page-header>

<form method="POST" action="{{ route('admin.cms.update', $page) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    @foreach($sections as $sectionKey => $section)
    <x-card :title="$section['label']">
        <div class="space-y-5">
            @foreach($section['fields'] as $field)
                @php
                    $name = "sections[{$sectionKey}][{$field['key']}]";
                    $value = old("sections.{$sectionKey}.{$field['key']}", $content[$sectionKey][$field['key']] ?? '');
                @endphp

                @switch($field['type'])
                    @case('text')
                        <x-form.input :name="$name" :label="$field['label']" :value="$value" />
                        @break

                    @case('url')
                        <x-form.input :name="$name" type="url" :label="$field['label']" :value="$value" />
                        @break

                    @case('textarea')
                        <x-form.textarea :name="$name" :label="$field['label']" :value="$value" rows="4" />
                        @break

                    @case('richtext')
                        <div>
                            <x-form.label :for="'field-'.$sectionKey.'-'.$field['key']">{{ $field['label'] }}</x-form.label>
                            <textarea name="{{ $name }}" id="field-{{ $sectionKey }}-{{ $field['key'] }}"
                                      data-jodit data-upload-url="{{ route('admin.media.jodit') }}">{{ $value }}</textarea>
                        </div>
                        @break

                    @case('image')
                        @php
                            $existingPath = $content[$sectionKey][$field['key']] ?? null;
                            $existingUrl = $existingPath ? \Illuminate\Support\Facades\Storage::disk('public')->url($existingPath) : '';
                        @endphp
                        @if($existingPath)
                            <input type="hidden" name="sections[{{ $sectionKey }}][{{ $field['key'] }}_existing]" value="{{ $existingPath }}">
                        @endif
                        <x-form.image :name="$name" :label="$field['label']" :current="$existingUrl" />
                        @break
                @endswitch
            @endforeach
        </div>
    </x-card>
    @endforeach

    @can('cms.edit')
    <div class="flex gap-2">
        <x-btn type="submit">Save page content</x-btn>
        <x-btn variant="outline" :href="route('admin.cms.index')">Cancel</x-btn>
    </div>
    @endcan
</form>
@endsection
