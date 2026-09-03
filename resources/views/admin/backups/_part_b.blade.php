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
                    <select name="frequency" x-model="form.frequency" class="{{ $input }}">
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
