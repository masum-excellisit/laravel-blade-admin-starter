<?php

namespace App\Models;

use App\Services\BackupManager;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'name', 'type', 'parts', 'path', 'size', 'status', 'error',
        'source', 'backup_schedule_id', 'is_protected', 'user_id', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'parts' => 'array',
        'size' => 'integer',
        'is_protected' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(BackupSchedule::class, 'backup_schedule_id');
    }

    public function absolutePath(): string
    {
        return storage_path('app/'.$this->path);
    }

    public function exists(): bool
    {
        return is_file($this->absolutePath());
    }

    public function getSizeForHumansAttribute(): string
    {
        return BackupManager::humanBytes((int) $this->size);
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->started_at || ! $this->completed_at) {
            return null;
        }

        $seconds = $this->completed_at->getTimestamp() - $this->started_at->getTimestamp();

        return $seconds < 60 ? "{$seconds}s" : floor($seconds / 60).'m '.($seconds % 60).'s';
    }
}
