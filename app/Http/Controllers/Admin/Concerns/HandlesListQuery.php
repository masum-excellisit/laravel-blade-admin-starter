<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HandlesListQuery
{
    /**
     * Apply search + sortable column ordering to a list query.
     *
     * @param  array<int, string>  $searchable  Column names (or "relation.column")
     * @param  array<int, string>  $sortable    Allowed sort columns
     */
    protected function applyListQuery(
        Builder $query,
        Request $request,
        array $searchable = ['title'],
        array $sortable = ['id', 'created_at', 'updated_at'],
        string $defaultSort = 'created_at',
        string $defaultDirection = 'desc',
    ): Builder {
        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function (Builder $q) use ($search, $searchable) {
                foreach ($searchable as $i => $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);
                        $method = $i === 0 ? 'whereHas' : 'orWhereHas';
                        $q->{$method}($relation, fn (Builder $rq) => $rq->where($relColumn, 'like', "%{$search}%"));
                    } else {
                        $method = $i === 0 ? 'where' : 'orWhere';
                        $q->{$method}($column, 'like', "%{$search}%");
                    }
                }
            });
        }

        $sort = $request->input('sort');
        $direction = strtolower((string) $request->input('direction', $defaultDirection)) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $sortable, true)) {
            $sort = $defaultSort;
            $direction = $defaultDirection;
        }

        return $query->orderBy($sort, $direction);
    }
}
