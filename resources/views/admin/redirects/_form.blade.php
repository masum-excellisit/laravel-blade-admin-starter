@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">
        <x-card title="Redirect">
            <div class="space-y-4">
                <x-form.input name="from_path" label="From path" :value="$redirect->from_path" required hint="Example: /old-page" />
                <x-form.input name="to_url" label="Destination URL" :value="$redirect->to_url" required hint="Use a relative path or full URL." />
            </div>
        </x-card>
    </div>
    <div class="space-y-5">
        <x-card title="Settings">
            <div class="space-y-4">
                <x-form.select name="status_code" label="Status code" :options="[
                    301 => '301 - Permanent',
                    302 => '302 - Temporary',
                    303 => '303 - See other',
                    307 => '307 - Temporary preserve method',
                    308 => '308 - Permanent preserve method',
                ]" :selected="$redirect->status_code ?? 301" />
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700 dark:text-slate-200">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $redirect->is_active ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Active
                </label>
                <x-btn type="submit" class="w-full">{{ $redirect->exists ? 'Update redirect' : 'Create redirect' }}</x-btn>
            </div>
        </x-card>
        @if($redirect->exists)
        <x-card title="Performance">
            <p class="text-sm text-slate-500 dark:text-slate-400">Hits</p>
            <p class="mt-1 text-3xl font-semibold">{{ number_format($redirect->hits) }}</p>
        </x-card>
        @endif
    </div>
</div>
