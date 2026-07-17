<x-card title="Recent revisions">
    @if($revisions->isEmpty())
        <p class="text-sm text-slate-400">No revisions recorded yet.</p>
    @else
        <div class="space-y-3">
            @foreach($revisions as $revision)
                <div class="rounded-xl border border-slate-200/70 dark:border-slate-700/60 p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">
                                {{ $revision->note ?: 'Revision' }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ $revision->created_at->diffForHumans() }}
                                @if($revision->user)
                                    by {{ $revision->user->name }}
                                @endif
                            </p>
                        </div>
                        @can($permission)
                            <form method="POST" action="{{ route($restoreRouteName, [$model, $revision]) }}" onsubmit="return confirm('Restore this revision? Current content will be replaced.')">
                                @csrf
                                <x-btn type="submit" size="sm" variant="outline">Restore</x-btn>
                            </form>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-card>
