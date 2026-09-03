@extends('layouts.admin')
@section('title', 'Backups')
@section('content')
@php
    $partLabels = ['database' => 'Database', 'storage' => 'Storage', 'code' => 'Codebase'];
    $partIcons = ['database' => 'database', 'storage' => 'folder', 'code' => 'code'];
    $typeCards = [
        'database' => ['icon' => 'database', 'tint' => 'text-sky-500 bg-sky-50 dark:bg-sky-900/30', 'text' => 'Every table, structure and rows, as a restorable SQL dump.', 'size' => $sources['database']],
        'storage' => ['icon' => 'folder', 'tint' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'Uploads and generated files under storage/app.', 'size' => $sources['storage']],
        'code' => ['icon' => 'code', 'tint' => 'text-violet-500 bg-violet-50 dark:bg-violet-900/30', 'text' => 'Application source, config and routes — no vendor or node_modules.', 'size' => $sources['code']],
        'full' => ['icon' => 'server', 'tint' => 'text-amber-500 bg-amber-50 dark:bg-amber-900/30', 'text' => 'Database, storage and codebase in one archive.', 'size' => $sources['database'] + $sources['storage'] + $sources['code']],
    ];
    $originLabels = [
        'manual' => 'Created by hand', 'scheduled' => 'Automatic', 'safety' => 'Safety copy',
        'upload' => 'Uploaded', 'imported' => 'Found on disk',
    ];
    $statusLabels = ['completed' => 'Ready', 'failed' => 'Failed', 'running' => 'In progress', 'pending' => 'Queued'];
    $alertTones = [
        'error' => ['bg-red-50 border-red-200 dark:bg-red-900/25 dark:border-red-900/40', 'text-red-600 dark:text-red-300'],
        'warning' => ['bg-amber-50 border-amber-200 dark:bg-amber-900/25 dark:border-amber-900/40', 'text-amber-600 dark:text-amber-300'],
        'info' => ['bg-sky-50 border-sky-200 dark:bg-sky-900/25 dark:border-sky-900/40', 'text-sky-600 dark:text-sky-300'],
    ];
    $input = 'w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5';
    $activeSchedules = $schedules->where('is_active', true)->count();
@endphp

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
            <dl class="grid grid-cols-1 gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
                @foreach([
                    'Operating system' => $system['os'],
                    'Machine' => $system['machine'],
                    'Host name' => $system['hostname'],
                    'Web server' => $system['server'],
                    'PHP' => $system['php'].' ('.$system['sapi'].')',
                    'Laravel' => $system['laravel'],
                    'Database' => $system['database'],
                    'Database name' => $system['database_name'],
                    'Memory limit' => $system['memory_limit'],
                    'Max execution time' => $system['max_execution_time'] === 0 ? 'unlimited' : $system['max_execution_time'].'s',
                ] as $term => $value)
                    <div>
                        <dt class="text-xs uppercase tracking-wider text-slate-400">{{ $term }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-700 dark:text-slate-200 break-words">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-5 flex flex-wrap gap-2">
                <x-badge :color="$system['zip'] ? 'green' : 'red'">{{ $system['zip'] ? 'ZIP extension available' : 'ZIP extension missing' }}</x-badge>
                <x-badge :color="$system['writable'] ? 'green' : 'red'">{{ $system['writable'] ? 'Backup folder writable' : 'Backup folder not writable' }}</x-badge>
                <x-badge color="slate">No shell tools required</x-badge>
            </div>
        </x-card>

        <x-card title="Storage location" class="lg:col-span-2">
            <p class="text-sm text-slate-500 dark:text-slate-400">Archives are written here and never exposed publicly:</p>
            <pre class="mt-2 overflow-x-auto rounded-xl bg-slate-900 px-4 py-3 text-xs text-slate-100"><code>{{ $stats['path'] }}</code></pre>
            <div class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Archives</p>
                    <p class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200">{{ $stats['archives'] }} files · {{ human_bytes($stats['archive_bytes']) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Volume free space</p>
                    <p class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200">{{ human_bytes($stats['disk_free']) }} of {{ human_bytes($stats['disk_total']) }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Retention default</p>
                    <p class="mt-0.5 font-semibold text-slate-700 dark:text-slate-200">Keep last {{ $retention }}</p>
                </div>
            </div>
        </x-card>
    </div>
</div>

{{-- ---------------------------------------------------------- Busy overlay --}}
<div x-show="working" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/70 backdrop-blur-sm">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-slate-800">
        <svg class="mx-auto h-10 w-10 animate-spin text-primary" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <p class="mt-4 font-semibold text-slate-800 dark:text-white" x-text="busyTitle"></p>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Large sites can take a few minutes. Please keep this tab open.
        </p>
        <p class="mt-3 font-mono text-sm text-slate-400" x-text="elapsedLabel"></p>
    </div>
</div>

{{-- -------------------------------------------------------- Confirm modal --}}
<div x-show="confirmOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     x-on:keydown.escape.window="closeConfirm()">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="closeConfirm()"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-800">
        <div class="flex items-start gap-3">
            <span class="rounded-xl p-2" :class="dialog.tone === 'danger' ? 'bg-red-50 text-red-500 dark:bg-red-900/30' : 'bg-primary-soft text-primary'">
                <x-icon name="bolt" class="w-5 h-5" />
            </span>
            <div class="min-w-0">
                <h3 class="font-semibold text-slate-800 dark:text-white" x-text="dialog.title"></h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-text="dialog.body"></p>
            </div>
        </div>

        <template x-if="dialog.ack">
            <label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                <input type="checkbox" x-model="ack" class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-slate-600 dark:text-slate-300">I understand this cannot be undone.</span>
            </label>
        </template>

        <template x-if="dialog.phrase">
            <div class="mt-3">
                <label class="mb-1.5 block text-sm text-slate-600 dark:text-slate-300">
                    Type <span class="font-mono font-semibold" x-text="dialog.phrase"></span> to confirm
                </label>
                <input type="text" x-model="typed" autocomplete="off" spellcheck="false"
                       x-init="$nextTick(() => $el.focus())"
                       x-on:keydown.enter.prevent="proceed()"
                       class="{{ $input }}" :placeholder="dialog.phrase">
            </div>
        </template>

        <div class="mt-6 flex justify-end gap-2">
            <x-btn type="button" variant="ghost" x-on:click="closeConfirm()">Cancel</x-btn>
            <button type="button" x-on:click="proceed()" :disabled="! canProceed || working"
                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-50"
                    :class="dialog.tone === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'brand-gradient hover:brightness-110'">
                <span x-text="working ? 'Working…' : dialog.label"></span>
            </button>
        </div>
    </div>
</div>

{{-- -------------------------------------------------------- Restore modal --}}
@can('backups.edit')
<div x-show="restoreOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     x-on:keydown.escape.window="restoreOpen = false">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="restoreOpen = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">
            <h3 class="font-semibold text-slate-800 dark:text-white">Restore from backup</h3>
            <span class="text-xs font-semibold text-slate-400" x-text="'Step ' + restoreStep + ' of 2'"></span>
        </div>

        <form method="POST" :action="restore.url" class="p-6" data-restore-form>
            @csrf

            <div x-show="restoreStep === 1">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Choose what to bring back from
                    <span class="font-medium text-slate-700 dark:text-slate-200" x-text="restore.name"></span>.
                </p>

                <div class="mt-4 space-y-2">
                    <template x-for="part in restore.parts" :key="part">
                        <label class="flex items-start gap-3 rounded-xl border p-3 transition"
                               :class="parts.includes(part) ? 'border-primary bg-primary-soft/40' : 'border-slate-200 dark:border-slate-700'">
                            <input type="checkbox" name="parts[]" :value="part" x-model="parts"
                                   class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span>
                                <span class="block text-sm font-medium text-slate-700 dark:text-slate-200" x-text="labels[part]"></span>
                                <span class="block text-xs text-slate-400" x-text="hints[part]"></span>
                            </span>
                        </label>
                    </template>
                </div>

                <label class="mt-4 flex items-center gap-3">
                    <input type="checkbox" name="safety_backup" value="1" x-model="safety"
                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-600 dark:text-slate-300">Create a safety backup first (recommended)</span>
                </label>

                <div class="mt-6 flex justify-end gap-2">
                    <x-btn type="button" variant="ghost" x-on:click="restoreOpen = false">Cancel</x-btn>
                    <x-btn type="button" x-on:click="restoreStep = 2" ::disabled="parts.length === 0">Continue</x-btn>
                </div>
            </div>

            <div x-show="restoreStep === 2">
                <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                    <p class="font-semibold">This overwrites live data.</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-4 text-xs">
                        <template x-for="part in parts" :key="part">
                            <li x-text="hints[part]"></li>
                        </template>
                        <li x-show="parts.includes('database')">You may be signed out — every table, including sessions, is replaced.</li>
                        <li x-show="safety">A safety backup is taken before anything is overwritten.</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <label class="mb-1.5 block text-sm text-slate-600 dark:text-slate-300">
                        Type <span class="font-mono font-semibold">RESTORE</span> to confirm
                    </label>
                    <input type="text" x-model="restoreTyped" autocomplete="off" spellcheck="false"
                           class="{{ $input }}" placeholder="RESTORE">
                </div>

                <label class="mt-3 flex items-start gap-3">
                    <input type="checkbox" x-model="restoreAck" class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-600 dark:text-slate-300">I understand the current data will be replaced.</span>
                </label>

                <div class="mt-6 flex justify-end gap-2">
                    <x-btn type="button" variant="ghost" x-on:click="restoreStep = 1">Back</x-btn>
                    <button type="submit" :disabled="restoreTyped !== 'RESTORE' || ! restoreAck || working"
                            x-on:click="restoreOpen = false; startWorking('Restoring your data…')"
                            class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                        <span x-text="working ? 'Restoring…' : 'Restore now'"></span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- ------------------------------------------------------- Schedule modal --}}
@can('backups.edit')
<div x-show="scheduleOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     x-on:keydown.escape.window="scheduleOpen = false">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="scheduleOpen = false"></div>
    <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
        <div class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-800 dark:border-slate-700 dark:text-white"
             x-text="form.id ? 'Edit schedule' : 'New schedule'"></div>

        <form method="POST" :action="form.id ? '{{ route('admin.backups.schedules.update', ['schedule' => 'SCHEDULE_ID']) }}'.replace('SCHEDULE_ID', form.id) : '{{ route('admin.backups.schedules.store') }}'"
              class="p-6"
              data-confirm-title="Save this schedule?"
              data-confirm-body="Automatic backups will run on the timing you picked. The server cron entry must be installed for it to fire."
              data-confirm-label="Save schedule">
            @csrf
            <input type="hidden" name="_method" :value="form.id ? 'PUT' : 'POST'">

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="{{ $label }}">Name</label>
                    <input type="text" name="name" x-model="form.name" required maxlength="100"
                           class="{{ $input }}" placeholder="Nightly database backup">
                </div>

                <div>
                    <label class="{{ $label }}">What to back up</label>
                    <select name="type" x-model="form.type" class="{{ $input }}">
                        @foreach($types as $value => $text)
                            <option value="{{ $value }}">{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="{{ $label }}">How often</label>
                    <select name="frequency" x-model="form.form.frequency" class="{{ $input }}">
                        @foreach(\App\Models\BackupSchedule::FREQUENCIES as $value => $text)
                            <option value="{{ $value }}">{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="form.frequency !== 'hourly'">
                    <label class="{{ $label }}">At time</label>
                    <input type="time" name="time" x-model="form.time" class="{{ $input }}">
                </div>

                <div x-show="form.frequency === 'weekly'">
                    <label class="{{ $label }}">Day of week</label>
                    <select name="day_of_week" x-model="form.day_of_week" :disabled="form.frequency !== 'weekly'" class="{{ $input }}">
                        @foreach(\App\Models\BackupSchedule::DAYS as $value => $text)
                            <option value="{{ $value }}">{{ $text }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="form.frequency === 'monthly'">
                    <label class="{{ $label }}">Day of month</label>
                    <input type="number" name="day_of_month" min="1" max="28" x-model="form.day_of_month"
                           :disabled="form.frequency !== 'monthly'" class="{{ $input }}">
                </div>

                <div>
                    <label class="{{ $label }}">Keep last</label>
                    <input type="number" name="retention" min="0" max="500" x-model="form.retention" class="{{ $input }}">
                    <p class="mt-1 text-xs text-slate-400">Older unlocked backups from this schedule are deleted.</p>
                </div>

                <div class="flex items-end">
                    <label class="flex items-center gap-3 pb-2">
                        <input type="checkbox" name="is_active" value="1" x-model="form.is_active"
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-600 dark:text-slate-300">Active</span>
                    </label>
                </div>
            </div>

            <p class="mt-4 rounded-xl bg-slate-50 px-3 py-2 font-mono text-xs text-slate-500 dark:bg-slate-900/40 dark:text-slate-400"
               x-text="'cron: ' + cronPreview"></p>

            <div class="mt-6 flex justify-end gap-2">
                <x-btn type="button" variant="ghost" x-on:click="scheduleOpen = false">Cancel</x-btn>
                <x-btn type="submit">Save schedule</x-btn>
            </div>
        </form>
    </div>
</div>
@endcan

{{-- -------------------------------------------------------- Cleanup modal --}}
@can('backups.delete')
<div x-show="cleanupOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4"
     x-on:keydown.escape.window="cleanupOpen = false">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" x-on:click="cleanupOpen = false"></div>
    <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-slate-800">
        <div class="border-b border-slate-200 px-6 py-4 font-semibold text-slate-800 dark:border-slate-700 dark:text-white">
            Clean up old backups
        </div>
        <form method="POST" action="{{ route('admin.backups.prune') }}" class="p-6"
              data-confirm-title="Delete old backups?"
              data-confirm-body="Everything beyond the newest few unlocked backups will be permanently deleted from disk. Locked backups are never touched."
              data-confirm-label="Delete old backups"
              data-confirm-tone="danger"
              data-confirm-ack="1"
              data-confirm-phrase="DELETE"
              data-confirm-busy="Deleting old backups…">
            @csrf
            <label class="{{ $label }}">Keep the newest</label>
            <input type="number" name="keep" min="1" max="500" value="{{ $retention }}" class="{{ $input }}">
            <p class="mt-2 text-xs text-slate-400">Locked backups are skipped and do not count toward this limit.</p>

            <div class="mt-6 flex justify-end gap-2">
                <x-btn type="button" variant="ghost" x-on:click="cleanupOpen = false">Cancel</x-btn>
                <x-btn type="submit" variant="danger">Clean up</x-btn>
            </div>
        </form>
    </div>
</div>
@endcan

</div>

@push('scripts')
<script>
    function backupsPage() {
        return {
            tab: 'library',
            working: false,
            busyTitle: 'Working…',
            elapsed: 0,
            timer: null,

            // Generic confirm dialog, driven by data-confirm-* attributes on any form.
            confirmOpen: false,
            dialog: { title: '', body: '', label: 'Confirm', tone: 'primary', ack: false, phrase: '', busy: '' },
            pending: null,
            ack: false,
            typed: '',

            // Restore
            restoreOpen: false,
            restoreStep: 1,
            restore: { name: '', parts: [], url: '' },
            parts: [],
            safety: true,
            restoreTyped: '',
            restoreAck: false,
            labels: @js($partLabels),
            hints: {
                database: 'Drops and recreates every table from the dump.',
                storage: 'Overwrites files under storage/app, including uploads.',
                code: 'Overwrites application source files in the project root.',
            },

            // Schedules
            scheduleOpen: false,
            form: {},

            init() {
                this.tab = sessionStorage.getItem('backups.tab') || 'library';
                this.form = this.blankSchedule();

                // Stop a stray click or refresh from interrupting a running backup.
                window.addEventListener('beforeunload', (event) => {
                    if (this.working) {
                        event.preventDefault();
                        event.returnValue = '';
                    }
                });
            },

            setTab(tab) {
                this.tab = tab;
                sessionStorage.setItem('backups.tab', tab);
            },

            guard(event) {
                const form = event.target;
                if (!(form instanceof HTMLFormElement) || form.dataset.confirmed === '1' || !form.dataset.confirmTitle) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                this.pending = form;
                this.dialog = {
                    title: form.dataset.confirmTitle,
                    body: form.dataset.confirmBody || '',
                    label: form.dataset.confirmLabel || 'Confirm',
                    tone: form.dataset.confirmTone || 'primary',
                    ack: form.dataset.confirmAck === '1',
                    phrase: form.dataset.confirmPhrase || '',
                    busy: form.dataset.confirmBusy || 'Working…',
                };
                this.ack = false;
                this.typed = '';
                this.confirmOpen = true;
            },

            get canProceed() {
                if (this.dialog.ack && !this.ack) return false;
                if (this.dialog.phrase && this.typed.trim() !== this.dialog.phrase) return false;
                return true;
            },

            closeConfirm() {
                this.confirmOpen = false;
                this.pending = null;
            },

            proceed() {
                if (!this.canProceed || !this.pending) return;
                const form = this.pending;
                form.dataset.confirmed = '1';
                this.confirmOpen = false;
                this.startWorking(this.dialog.busy);
                if (form.requestSubmit) {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            },

            startWorking(title) {
                this.busyTitle = title || 'Working…';
                this.working = true;
                this.elapsed = 0;
                clearInterval(this.timer);
                this.timer = setInterval(() => this.elapsed++, 1000);
            },

            get elapsedLabel() {
                const minutes = String(Math.floor(this.elapsed / 60)).padStart(2, '0');
                const seconds = String(this.elapsed % 60).padStart(2, '0');
                return `${minutes}:${seconds} elapsed`;
            },

            openRestore(backup) {
                this.restore = backup;
                this.parts = [...backup.parts];
                this.safety = true;
                this.restoreStep = 1;
                this.restoreTyped = '';
                this.restoreAck = false;
                this.working = false;
                this.restoreOpen = true;
            },

            blankSchedule() {
                return {
                    id: null, name: '', type: 'full', frequency: 'daily', time: '02:00',
                    day_of_week: 0, day_of_month: 1, retention: 10, is_active: true,
                };
            },

            newSchedule() {
                this.form = this.blankSchedule();
                this.scheduleOpen = true;
            },

            editSchedule(schedule) {
                this.form = { ...this.blankSchedule(), ...schedule };
                this.scheduleOpen = true;
            },

            get cronPreview() {
                const [hour, minute] = (this.form.time || '02:00').split(':').map((v) => parseInt(v, 10) || 0);
                switch (this.form.frequency) {
                    case 'hourly': return `${minute} * * * *`;
                    case 'weekly': return `${minute} ${hour} * * ${this.form.day_of_week}`;
                    case 'monthly': return `${minute} ${hour} ${this.form.day_of_month} * *`;
                    default: return `${minute} ${hour} * * *`;
                }
            },
        };
    }
</script>
@endpush
@endsection
