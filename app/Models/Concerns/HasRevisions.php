<?php

namespace App\Models\Concerns;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRevisions
{
    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    public function recordRevision(?string $note = null): Revision
    {
        return $this->revisions()->create([
            'user_id' => auth()->id(),
            'payload' => $this->getAttributes(),
            'note' => $note,
        ]);
    }

    public function restoreRevision(Revision $revision): bool
    {
        return $this->fill($revision->payload ?? [])->save();
    }
}
