@props(['headings' => []])
<div class="overflow-x-auto rounded-2xl border border-slate-200/70 dark:border-slate-700/60">
    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700 text-sm">
        @if(count($headings))
        <thead class="bg-slate-50 dark:bg-slate-800/80">
            <tr>
                @foreach($headings as $h)
                <th class="px-4 py-3 text-left font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-xs">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        @endif
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 bg-white dark:bg-slate-800/40">
            {{ $slot }}
        </tbody>
    </table>
</div>
