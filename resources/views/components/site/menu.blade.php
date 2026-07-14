@props(['location' => 'header', 'class' => ''])
@php $menu = \App\Models\Menu::where('location', $location)->with('rootItems.children')->first(); @endphp
@if($menu)
    @foreach($menu->rootItems as $item)
        <a href="{{ $item->url() }}" class="{{ $class }}">{{ $item->label }}</a>
    @endforeach
@endif
