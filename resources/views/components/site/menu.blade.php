@props(['location' => 'header', 'class' => ''])
@php $menu = \App\Models\Menu::where('location', $location)->with('rootItems.children')->first(); @endphp
@if($menu)
    @foreach($menu->rootItems as $item)
        @if($item->children->count())
            <div class="relative group">
                <a href="{{ $item->url() }}" class="{{ $class }} inline-flex items-center gap-1">
                    {{ $item->label }}
                    <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </a>
                <div class="absolute left-0 top-full pt-2 hidden group-hover:block z-30">
                    <div class="min-w-[11rem] rounded-xl border border-slate-200 bg-white shadow-lg py-2">
                        @foreach($item->children as $child)
                            <a href="{{ $child->url() }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ $child->label }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <a href="{{ $item->url() }}" class="{{ $class }}">{{ $item->label }}</a>
        @endif
    @endforeach
@endif
