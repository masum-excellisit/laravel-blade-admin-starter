{{-- WIDGET: latest posts. Needs $recentPosts. --}}
<section class="ez-panel">
    <header class="ez-panel__head">
        <div>
            <h3 class="ez-panel__title">Recent posts</h3>
            <p class="ez-panel__sub">Latest {{ $recentPosts->count() ?: '' }} entries</p>
        </div>
        @can('posts.view')
            <a href="{{ route('admin.posts.index') }}" class="ez-link">All posts <x-icon name="chevron-right" class="w-3.5 h-3.5" /></a>
        @endcan
    </header>
    <div class="ez-panel__body">
        @if($recentPosts->isEmpty())
            <x-empty-state icon="pencil" message="No posts yet." />
        @else
        <ul class="ez-list">
            @foreach($recentPosts as $post)
                <li class="ez-list__row">
                    <span class="ez-list__mark" style="--tint: var(--c-primary)"><x-icon name="pencil" class="w-4 h-4" /></span>
                    <div class="ez-list__main">
                        @can('posts.view')
                            <a href="{{ route('admin.posts.edit', $post) }}" class="ez-list__title">{{ $post->title }}</a>
                        @else
                            <p class="ez-list__title">{{ $post->title }}</p>
                        @endcan
                        <p class="ez-list__meta">{{ $post->created_at?->diffForHumans() }}</p>
                    </div>
                    <x-badge :color="$post->status === 'published' ? 'green' : 'slate'">{{ $post->status }}</x-badge>
                </li>
            @endforeach
        </ul>
        @endif
    </div>
</section>
