<a href="{{ route('blog.show', $post->slug) }}" class="group block rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-lg transition">
    <div class="aspect-[16/10] overflow-hidden bg-slate-100">
        @if($post->featured_image)
        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($post->featured_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
        @else
        <div class="w-full h-full brand-gradient opacity-90"></div>
        @endif
    </div>
    <div class="p-6">
        @if($post->category)<span class="text-xs font-semibold brand-gradient-text uppercase tracking-wide">{{ $post->category->name }}</span>@endif
        <h3 class="mt-2 font-semibold text-lg leading-snug group-hover:text-primary transition">{{ $post->title }}</h3>
        <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ $post->excerpt }}</p>
        <p class="mt-4 text-xs text-slate-400">{{ $post->published_at?->format('M j, Y') }} · {{ $post->author?->name }}</p>
    </div>
</a>
