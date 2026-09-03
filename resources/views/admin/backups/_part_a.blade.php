<div x-data="backupsPage()" x-on:submit.capture="guard($event)">

<x-page-header title="Backups" subtitle="Create, schedule, download and restore full system backups.">
    <x-slot:actions>
        @can('backups.create')
            <form method="POST" action="{{ route('admin.backups.rescan') }}"
                  data-confirm-title="Rescan the backup folder?"
                  data-confirm-body="Any archive sitting in the backups folder that is missing from this list will be registered."
                  data-confirm-label="Rescan now"
                  data-confirm-busy="Scanning the backup folder…">
                @csrf
                <x-btn type="submit" variant="outline"><x-icon name="restore" class="w-4 h-4" /> Rescan folder</x-btn>
            </form>
        @endcan
        @can('backups.delete')
            <x-btn variant="outline" x-on:click="cleanupOpen = true"><x-icon name="trash" class="w-4 h-4" /> Clean up</x-btn>
        @endcan
    </x-slot:actions>
</x-page-header>

{{-- --------------------------------------------------------------- Alerts --}}
@if(count($alerts))
<div class="mb-6 space-y-2">
    @foreach($alerts as $alert)
        @php [$box, $icon] = $alertTones[$alert['tone']]; @endphp
        <div class="flex items-start gap-3 rounded-2xl border px-4 py-3 {{ $box }}">
            <x-icon :name="$alert['tone'] === 'info' ? 'sparkles' : 'bolt'" class="mt-0.5 h-5 w-5 shrink-0 {{ $icon }}" />
            <div class="text-sm">
                <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $alert['title'] }}</p>
                <p class="mt-0.5 text-slate-600 dark:text-slate-300">{{ $alert['body'] }}</p>
            </div>
        </div>
    @endforeach
</div>
@endif

{{-- ---------------------------------------------------------------- Stats --}}
<div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-card :padded="false">
        <div class="p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Backups stored</p>
                <span class="rounded-lg bg-primary-soft p-1.5 text-primary"><x-icon name="server" class="w-4 h-4" /></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">{{ $stats['count'] }}</p>
            <p class="mt-2 text-xs text-slate-400">{{ human_bytes($stats['archive_bytes']) }} in {{ $stats['archives'] }} archive(s)</p>
            <p class="text-xs {{ $stats['failed'] > 0 ? 'text-red-500' : 'text-slate-400' }}">
                {{ $stats['failed'] > 0 ? $stats['failed'].' failed' : 'no failures' }}
            </p>
        </div>
    </x-card>

    <x-card :padded="false">
        <div class="p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Disk usage</p>
                <span class="rounded-lg bg-primary-soft p-1.5 text-primary"><x-icon name="bolt" class="w-4 h-4" /></span>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">
                {{ $stats['disk_total'] > 0 ? $stats['disk_used_percent'].'%' : 'n/a' }}
            </p>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                <div class="h-full rounded-full {{ $stats['disk_used_percent'] > 90 ? 'bg-red-500' : ($stats['disk_used_percent'] > 75 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                     style="width: {{ min(100, max(1, $stats['disk_used_percent'])) }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-400">{{ human_bytes($stats['disk_free']) }} free</p>
            <p class="text-xs text-slate-400">of {{ human_bytes($stats['disk_total']) }} total</p>
        </div>
    </x-card>

    <x-card :padded="false">
        <div class="p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Live data size</p>
                <form method="GET" action="{{ route('admin.backups.index') }}"
                      data-confirm-title="Recalculate live data size?"
                      data-confirm-body="This rescans database, storage, and code sizes. It may take a moment on large projects."
                      data-confirm-label="Recalculate"
                      data-confirm-busy="Recalculating…">
                    <input type="hidden" name="refresh" value="1">
                    <button type="submit" class="text-xs font-semibold text-primary hover:underline">Recalculate</button>
                </form>
            </div>
            <p class="mt-2 text-2xl font-bold text-slate-800 dark:text-white">
                {{ human_bytes($sources['database'] + $sources['storage'] + $sources['code']) }}
            </p>
            <p class="mt-2 text-xs text-slate-400">DB {{ human_bytes($sources['database']) }} · Storage {{ human_bytes($sources['storage']) }}</p>
            <p class="text-xs text-slate-400">Code {{ human_bytes($sources['code']) }}</p>
        </div>
    </x-card>

    <x-card :padded="false">
        <div class="p-4">
            <div class="flex items-start justify-between">
                <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Automatic backups</p>
                <span class="rounded-lg bg-primary-soft p-1.5 text-primary"><x-icon name="clock" class="w-4 h-4" /></span>
            </div>
            <p class="mt-2 text-2xl font-bold {{ $activeSchedules ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">
                {{ $activeSchedules }}<span class="text-base font-medium text-slate-400"> active</span>
            </p>
            @if($nextRun)
                <p class="mt-2 text-xs text-slate-400">Next run {{ $nextRun->diffForHumans() }}</p>
                <p class="text-xs text-slate-400">{{ $nextRun->format('d M Y, H:i') }}</p>
            @else
                <p class="mt-2 text-xs text-slate-400">No schedule is running</p>
            @endif
        </div>
    </x-card>
</div>

{{-- ----------------------------------------------------------------- Tabs --}}
<div class="mb-5 inline-flex flex-wrap gap-1 rounded-2xl border border-slate-200/70 bg-white p-1 dark:border-slate-700/60 dark:bg-slate-800/60">
    @foreach([
        'library' => ['Backup library', $stats['count']],
        'schedules' => ['Schedules', $schedules->count()],
        'system' => ['Storage & system', null],
    ] as $key => [$text, $count])
        <button type="button" x-on:click="setTab('{{ $key }}')"
                :class="tab === '{{ $key }}' ? 'bg-primary-soft text-primary' : 'text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-700/60'"
                class="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition">
            {{ $text }}
            @if($count !== null)
                <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-xs font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">{{ $count }}</span>
            @endif
        </button>
    @endforeach
</div>

{{-- -------------------------------------------------------------- Library --}}
<div x-show="tab === 'library'" x-cloak>
    @can('backups.create')
    <x-card title="Create a backup" class="mb-6">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($typeCards as $type => $meta)
                <form method="POST" action="{{ route('admin.backups.store') }}"
                      data-confirm-title="Create a {{ strtolower($types[$type]) }} backup?"
                      data-confirm-body="This runs now and may take a while — roughly {{ human_bytes($meta['size']) }} of data will be archived. Keep this tab open until it finishes."
                      data-confirm-label="Start backup"
                      data-confirm-busy="Creating your backup…">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <button type="submit" :disabled="working"
                            class="group flex h-full w-full flex-col rounded-2xl border border-slate-200/70 p-4 text-left transition hover:border-primary hover:shadow-md disabled:cursor-wait disabled:opacity-60 dark:border-slate-700/60 dark:hover:border-primary">
                        <span class="flex items-center gap-2.5">
                            <span class="inline-flex rounded-lg p-1.5 {{ $meta['tint'] }}">
                                <x-icon :name="$meta['icon']" class="w-4 h-4" />
                            </span>
                            <span class="font-semibold text-slate-800 dark:text-white">{{ $types[$type] }}</span>
                            <span class="ml-auto text-xs font-medium text-slate-400">≈ {{ human_bytes($meta['size']) }}</span>
                        </span>
                        <span class="mt-2 block flex-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $meta['text'] }}</span>
                        <span class="mt-3 inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition group-hover:bg-primary group-hover:text-white dark:bg-slate-700 dark:text-slate-200">
                            <x-icon name="download" class="w-3.5 h-3.5" /> Back up now
                        </span>
                    </button>
                </form>
            @endforeach
        </div>

        <div class="mt-5 border-t border-slate-200/70 pt-5 dark:border-slate-700/60" x-data="{ file: '' }">
            <form method="POST" action="{{ route('admin.backups.upload') }}" enctype="multipart/form-data"
                  data-confirm-title="Upload this archive?"
                  data-confirm-body="The file is stored in the backup folder and its manifest is validated. Nothing is restored until you choose to restore it."
                  data-confirm-label="Upload archive"
                  data-confirm-busy="Uploading the archive…">
                @csrf
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <label for="archive"
                           class="flex flex-1 cursor-pointer items-center gap-3 rounded-xl border border-dashed border-slate-300 px-4 py-3 transition hover:border-primary hover:bg-primary-soft/30 dark:border-slate-600">
                        <x-icon name="upload" class="w-5 h-5 shrink-0 text-slate-400" />
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-slate-700 dark:text-slate-200"
                                  x-text="file || 'Choose a .zip backup archive to upload'"></span>
                            <span class="block text-xs text-slate-400">Archives from another environment work too — the manifest is checked on upload.</span>
                        </span>
                        <input type="file" name="archive" id="archive" accept=".zip" required class="sr-only"
                               x-on:change="file = $event.target.files[0]?.name || ''">
                    </label>
                    <x-btn type="submit" variant="outline" ::disabled="! file"><x-icon name="upload" class="w-4 h-4" /> Upload</x-btn>
                </div>
                @error('archive')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </form>
        </div>
    </x-card>
    @endcan

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.backups.index') }}"
          class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                <x-icon name="search" class="w-4 h-4" />
            </span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search backups by file name…"
                   class="{{ $input }} pl-9">
        </div>
        <select name="type" class="{{ $input }} sm:w-44">
            <option value="">All types</option>
            @foreach($types as $value => $text)
                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $text }}</option>
            @endforeach
        </select>
        <select name="source" class="{{ $input }} sm:w-44">
            <option value="">All origins</option>
            @foreach($sourceOptions as $value)
                <option value="{{ $value }}" @selected(request('source') === $value)>{{ ucfirst($value) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <x-btn type="submit" variant="secondary">Filter</x-btn>
            @if($filtered)
                <x-btn :href="route('admin.backups.index')" variant="ghost">Clear</x-btn>
            @endif
        </div>
    </form>

    @if(count($backups) === 0)
        <x-card>
            <div class="py-12 text-center">
                <x-icon name="server" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" />
                @if($filtered)
                    <p class="mt-3 font-medium text-slate-500">Nothing matches those filters</p>
                    <p class="mt-1 text-sm text-slate-400">Try a different search term, or clear the filters.</p>
                    <div class="mt-4"><x-btn :href="route('admin.backups.index')" variant="outline">Clear filters</x-btn></div>
                @else
                    <p class="mt-3 font-medium text-slate-500">No backups yet</p>
                    <p class="mt-1 text-sm text-slate-400">Pick a type above to create your first one, or upload an archive from another environment.</p>
                @endif
            </div>
        </x-card>
    @else
        {{-- Desktop table --}}
        <div class="hidden lg:block">
            <x-table :columns="[
                ['key' => null, 'label' => 'Backup', 'sortable' => false],
                ['key' => null, 'label' => 'Contents', 'sortable' => false],
                ['key' => null, 'label' => 'Size', 'sortable' => false],
                ['key' => null, 'label' => 'Origin', 'sortable' => false],
                ['key' => null, 'label' => 'Created', 'sortable' => false],
                ['key' => null, 'label' => 'Status', 'sortable' => false],
                ['key' => null, 'label' => 'Actions', 'sortable' => false],
            ]">
                @foreach($backups as $backup)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 font-medium text-slate-800 dark:text-slate-100">
                            <span class="max-w-[16rem] truncate" title="{{ $backup->name }}">{{ $backup->name }}</span>
                            @if($backup->is_protected)
                                <span title="Locked — cleanup skips this one"><x-icon name="lock" class="w-4 h-4 text-amber-500" /></span>
                            @endif
                        </div>
                        <div class="text-xs text-slate-400">
                            by {{ $backup->user?->name ?? 'the system' }}
                            @if($backup->duration) · took {{ $backup->duration }} @endif
                            @unless($backup->exists()) · <span class="text-red-500">file missing from disk</span> @endunless
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @foreach($backup->parts ?? [] as $part)
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    <x-icon :name="$partIcons[$part] ?? 'document'" class="w-3.5 h-3.5" />{{ $partLabels[$part] ?? $part }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $backup->size_for_humans }}</td>
                    <td class="px-4 py-3">
                        <x-badge :color="$backup->source === 'safety' ? 'amber' : 'slate'">{{ $originLabels[$backup->source] ?? $backup->source }}</x-badge>
                        @if($backup->schedule)
                            <div class="mt-1 max-w-[10rem] truncate text-xs text-slate-400">{{ $backup->schedule->name }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-500">
                        {{ $backup->created_at->format('d M Y H:i') }}
                        <div class="text-xs text-slate-400">{{ $backup->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge :color="match($backup->status) { 'completed' => 'green', 'failed' => 'red', default => 'amber' }">
                            {{ $statusLabels[$backup->status] ?? $backup->status }}
                        </x-badge>
                        @if($backup->error)
                            <div class="mt-1 max-w-xs truncate text-xs text-red-500" title="{{ $backup->error }}">{{ $backup->error }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end">
                            @include('admin.backups.partials.actions', ['backup' => $backup])
                        </div>
                    </td>
                </tr>
                @endforeach
            </x-table>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 lg:hidden">
            @foreach($backups as $backup)
                <x-card :padded="false">
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="truncate font-medium text-slate-800 dark:text-slate-100">{{ $backup->name }}</span>
                                    @if($backup->is_protected)<x-icon name="lock" class="w-4 h-4 shrink-0 text-amber-500" />@endif
                                </div>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $backup->created_at->diffForHumans() }} · {{ $backup->size_for_humans }} ·
                                    {{ $originLabels[$backup->source] ?? $backup->source }}
                                </p>
                            </div>
                            <x-badge :color="match($backup->status) { 'completed' => 'green', 'failed' => 'red', default => 'amber' }">
                                {{ $statusLabels[$backup->status] ?? $backup->status }}
                            </x-badge>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-1">
                            @foreach($backup->parts ?? [] as $part)
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-200">
                                    <x-icon :name="$partIcons[$part] ?? 'document'" class="w-3.5 h-3.5" />{{ $partLabels[$part] ?? $part }}
                                </span>
                            @endforeach
                        </div>

                        @if($backup->error)
                            <p class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600 dark:bg-red-900/30 dark:text-red-300">{{ $backup->error }}</p>
                        @endif

                        <div class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-700/60">
                            @include('admin.backups.partials.actions', ['backup' => $backup])
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif

    <div class="mt-4">{{ $backups->links() }}</div>
</div>

{{-- ------------------------------------------------------------ Schedules --}}
<div x-show="tab === 'schedules'" x-cloak>
    <x-card class="mb-6" :padded="false">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/70 px-6 py-4 dark:border-slate-700/60">
            <div>
                <h3 class="font-semibold text-slate-800 dark:text-slate-100">Automatic scheduled backups</h3>
                <p class="mt-0.5 text-xs text-slate-400">Add as many schedules as you need — for example a nightly database backup plus a weekly full backup.</p>
            </div>
            @can('backups.edit')
                <x-btn size="sm" x-on:click="newSchedule()"><x-icon name="plus" class="w-4 h-4" /> Add schedule</x-btn>
            @endcan
        </div>

        <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
            @forelse($schedules as $schedule)
                @php $next = $schedule->nextRunAt(); @endphp
                <div class="flex flex-col gap-3 px-6 py-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="mt-0.5 rounded-xl p-2 {{ $schedule->is_active ? 'bg-emerald-50 text-emerald-500 dark:bg-emerald-900/30' : 'bg-slate-100 text-slate-400 dark:bg-slate-700' }}">
                            <x-icon name="clock" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-800 dark:text-white">{{ $schedule->name }}</span>
                                <x-badge :color="$schedule->is_active ? 'green' : 'slate'">{{ $schedule->is_active ? 'active' : 'paused' }}</x-badge>
                                <x-badge color="indigo">{{ $types[$schedule->type] ?? $schedule->type }}</x-badge>
                            </div>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $schedule->summary }} · keeps last {{ $schedule->retention }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ $schedule->backups_count }} backup(s) from this schedule ·
                                Last run: {{ $schedule->last_run_at?->diffForHumans() ?? 'never' }}
                                @if($schedule->is_active && $next) · Next: {{ $next->format('d M Y H:i') }} @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-1 lg:justify-end">
                        @can('backups.create')
                            <form method="POST" action="{{ route('admin.backups.schedules.run', $schedule) }}"
                                  data-confirm-title="Run “{{ $schedule->name }}” now?"
                                  data-confirm-body="A {{ strtolower($types[$schedule->type] ?? $schedule->type) }} backup starts immediately and old backups from this schedule beyond {{ $schedule->retention }} are cleaned up."
                                  data-confirm-label="Run now"
                                  data-confirm-busy="Running the schedule…">
                                @csrf
                                <x-btn type="submit" size="sm" variant="outline"><x-icon name="bolt" class="w-4 h-4" /> Run now</x-btn>
                            </form>
                        @endcan
                        @can('backups.edit')
                            <x-icon-btn icon="edit" label="Edit"
                                        x-on:click="editSchedule({{ Js::from([
                                            'id' => $schedule->id,
                                            'name' => $schedule->name,
                                            'type' => $schedule->type,
                                            'frequency' => $schedule->frequency,
                                            'time' => $schedule->time,
                                            'day_of_week' => $schedule->day_of_week,
                                            'day_of_month' => $schedule->day_of_month,
                                            'retention' => $schedule->retention,
                                            'is_active' => $schedule->is_active,
                                        ]) }})" />
                            <form method="POST" action="{{ route('admin.backups.schedules.toggle', $schedule) }}"
                                  data-confirm-title="{{ $schedule->is_active ? 'Pause' : 'Resume' }} “{{ $schedule->name }}”?"
                                  data-confirm-body="{{ $schedule->is_active ? 'It stops running until you resume it. Existing backups are kept.' : 'It starts running again on its cron schedule.' }}"
                                  data-confirm-label="{{ $schedule->is_active ? 'Pause' : 'Resume' }}">
                                @csrf
                                <x-icon-btn :icon="$schedule->is_active ? 'x' : 'check'" type="submit"
                                            :label="$schedule->is_active ? 'Pause' : 'Resume'" />
                            </form>
                        @endcan
                        @can('backups.delete')
                            <form method="POST" action="{{ route('admin.backups.schedules.destroy', $schedule) }}"
                                  data-confirm-title="Delete schedule “{{ $schedule->name }}”?"
                                  data-confirm-body="The schedule stops running. Backups it already created are kept."
                                  data-confirm-label="Delete schedule"
                                  data-confirm-tone="danger"
                                  data-confirm-ack="1"
                                  data-confirm-phrase="DELETE">
                                @csrf
                                @method('DELETE')
                                <x-icon-btn icon="trash" type="submit" variant="danger" label="Delete" />
                            </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <x-icon name="clock" class="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" />
                    <p class="mt-3 font-medium text-slate-500">No schedules yet</p>
                    <p class="mt-1 text-sm text-slate-400">Add one to back up automatically — hourly, daily, weekly or monthly.</p>
                </div>
            @endforelse
        </div>
    </x-card>

    <x-card title="Server requirement">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Schedules run through Laravel's scheduler. Add this single cron entry (or the equivalent Task Scheduler entry on Windows) once:
        </p>
        <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-900 px-4 py-3 text-xs text-slate-100"><code>* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1</code></pre>
        <p class="mt-3 text-xs text-slate-400">Detected: {{ $system['machine'] }} · {{ $system['server'] }} · PHP {{ $system['php'] }} ({{ $system['sapi'] }})</p>
    </x-card>
</div>

{{-- --------------------------------------------------------------- System --}}
<div x-show="tab === 'system'" x-cloak>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-card title="What gets backed up">
            <div class="space-y-4">
                @foreach(['database' => 'Database', 'storage' => 'Storage (storage/app)', 'code' => 'Codebase (project root)'] as $key => $text)
                    @php
                        $total = max(1, $sources['database'] + $sources['storage'] + $sources['code']);
                        $percent = round(($sources[$key] / $total) * 100);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="flex items-center gap-2 font-medium text-slate-700 dark:text-slate-200">
                                <x-icon :name="$partIcons[$key]" class="w-4 h-4 text-primary" />{{ $text }}
                            </span>
                            <span class="text-slate-500">{{ human_bytes($sources[$key]) }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                            <div class="h-full rounded-full brand-gradient" style="width: {{ max(1, $percent) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-5 text-xs text-slate-400">
                Measured {{ $sources['measured_at'] }} · cached for 10 minutes ·
                <a href="{{ route('admin.backups.index', ['refresh' => 1]) }}" class="font-semibold text-primary hover:underline">recalculate now</a>
            </p>
        </x-card>

        <x-card title="Detected environment">
