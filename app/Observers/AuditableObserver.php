<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Support\Activity;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->record('created', $model);
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())
            ->except(['updated_at', 'remember_token', 'password', 'last_login_at'])
            ->keys()
            ->values()
            ->all();

        if ($changes === []) {
            return;
        }

        $this->record('updated', $model, ['changed' => $changes]);
    }

    public function deleted(Model $model): void
    {
        $this->record('deleted', $model, $this->summary($model));
    }

    protected function record(string $action, Model $model, array $properties = []): void
    {
        if ($model instanceof ActivityLog) {
            return;
        }

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        $label = str($model->getTable())->replace('_', ' ')->singular()->title();

        Activity::log(
            $action,
            $model,
            trim($label.' '.$action),
            $properties,
        );
    }

    protected function summary(Model $model): array
    {
        $attrs = [];
        foreach (['name', 'title', 'email', 'label', 'slug', 'question'] as $key) {
            if ($model->getAttribute($key) !== null) {
                $attrs[$key] = $model->getAttribute($key);
            }
        }

        return $attrs;
    }
}
