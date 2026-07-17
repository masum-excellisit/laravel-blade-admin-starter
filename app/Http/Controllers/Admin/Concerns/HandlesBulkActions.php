<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HandlesBulkActions
{
    /**
     * Run a bulk action against selected IDs.
     *
     * @param  callable(Builder, string, array<int, int|string>): void|null  $handler
     */
    protected function runBulkAction(
        Request $request,
        string $modelClass,
        string $permissionPrefix,
        ?callable $handler = null,
        array $extraActions = [],
    ): RedirectResponse {
        abort_unless($request->user()->can("{$permissionPrefix}.delete")
            || $request->user()->can("{$permissionPrefix}.edit"), 403);

        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'string'],
        ]);

        $ids = $data['ids'];
        $action = $data['action'];
        /** @var Model $modelClass */
        $query = $modelClass::query()->whereIn('id', $ids);

        if ($handler) {
            $handler($query, $action, $ids);
        } else {
            match ($action) {
                'delete' => tap($query)->get()->each(function (Model $model) use ($request, $permissionPrefix) {
                    abort_unless($request->user()->can("{$permissionPrefix}.delete"), 403);
                    $model->delete();
                }),
                'publish' => $this->bulkSetStatus($request, $query, $permissionPrefix, 'published'),
                'draft' => $this->bulkSetStatus($request, $query, $permissionPrefix, 'draft'),
                'activate' => $this->bulkSetStatus($request, $query, $permissionPrefix, true, 'status'),
                'deactivate' => $this->bulkSetStatus($request, $query, $permissionPrefix, false, 'status'),
                default => abort(422, 'Unknown bulk action.'),
            };
        }

        $count = count($ids);

        return back()->with('success', "Bulk action \"{$action}\" applied to {$count} item(s).");
    }

    protected function bulkSetStatus(Request $request, Builder $query, string $permissionPrefix, mixed $value, string $column = 'status'): void
    {
        abort_unless($request->user()->can("{$permissionPrefix}.edit"), 403);
        $query->update([$column => $value]);
    }
}
