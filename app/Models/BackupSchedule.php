<?php

namespace App\Models;

use Cron\CronExpression;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Throwable;

class BackupSchedule extends Model
{
    public const FREQUENCIES = [
        'hourly' => 'Every hour',
        'daily' => 'Every day',
        'weekly' => 'Every week',
        'monthly' => 'Every month',
    ];

    public const DAYS = [
        0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday',
        4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
    ];

    protected $fillable = [
        'name', 'type', 'frequency', 'time', 'day_of_week', 'day_of_month',
        'retention', 'is_active', 'last_run_at',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
        'retention' => 'integer',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    public function cronExpression(): string
    {
        [$hour, $minute] = array_pad(explode(':', $this->time ?: '02:00'), 2, '0');
        $hour = (int) $hour;
        $minute = (int) $minute;

        return match ($this->frequency) {
            'hourly' => "{$minute} * * * *",
            'weekly' => "{$minute} {$hour} * * {$this->day_of_week}",
            'monthly' => "{$minute} {$hour} {$this->day_of_month} * *",
            default => "{$minute} {$hour} * * *",
        };
    }

    public function nextRunAt(): ?Carbon
    {
        try {
            return Carbon::instance((new CronExpression($this->cronExpression()))->getNextRunDate(
                Carbon::now(config('app.timezone'))
            ));
        } catch (Throwable) {
            return null;
        }
    }

    public function getSummaryAttribute(): string
    {
        return match ($this->frequency) {
            'hourly' => 'Every hour at :'.str_pad((string) (int) explode(':', $this->time)[1], 2, '0', STR_PAD_LEFT),
            'weekly' => 'Every '.(self::DAYS[$this->day_of_week] ?? 'Sunday').' at '.$this->time,
            'monthly' => 'Day '.$this->day_of_month.' of each month at '.$this->time,
            default => 'Every day at '.$this->time,
        };
    }
}
