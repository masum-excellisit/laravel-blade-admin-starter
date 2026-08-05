{{-- Row actions, shared by the desktop table and the mobile card list. --}}
<div class="flex items-center gap-1">
    @if($backup->status === 'completed' && $backup->exists())
        <x-icon-btn icon="download" :href="route('admin.backups.download', $backup)" label="Download this backup" />

        @can('backups.edit')
            <x-icon-btn icon="restore" variant="view" label="Restore from this backup"
                        x-on:click="openRestore({{ Js::from([
                            'name' => $backup->name,
                            'parts' => $backup->parts ?? [],
                            'url' => route('admin.backups.restore', $backup),
                        ]) }})" />
        @endcan
    @endif

    @can('backups.edit')
        <form method="POST" action="{{ route('admin.backups.protect', $backup) }}"
              data-confirm-title="{{ $backup->is_protected ? 'Unlock this backup?' : 'Lock this backup?' }}"
              data-confirm-body="{{ $backup->is_protected ? 'Cleanup will be allowed to delete it again once it falls outside the retention limit.' : 'It will be kept forever and skipped by every cleanup and schedule.' }}"
              data-confirm-label="{{ $backup->is_protected ? 'Unlock it' : 'Lock it' }}">
            @csrf
            <x-icon-btn :icon="$backup->is_protected ? 'lock' : 'unlock'" type="submit"
                        :label="$backup->is_protected ? 'Unlock — allow cleanup to delete it' : 'Lock — keep this one forever'" />
        </form>
    @endcan

    @can('backups.delete')
        <form method="POST" action="{{ route('admin.backups.destroy', $backup) }}"
              data-confirm-title="Delete this backup?"
              data-confirm-body="{{ $backup->name }} ({{ $backup->size_for_humans }}) will be permanently deleted from disk. This cannot be undone."
              data-confirm-label="Delete permanently"
              data-confirm-tone="danger"
              data-confirm-ack="1"
              data-confirm-phrase="DELETE">
            @csrf
            @method('DELETE')
            <x-icon-btn icon="trash" type="submit" variant="danger" label="Delete this backup" />
        </form>
    @endcan
</div>
