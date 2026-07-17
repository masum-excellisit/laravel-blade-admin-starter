<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $modules = Permission::orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0]);

        return view('admin.permissions.index', compact('modules'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Permission::class, 'permissions');
    }

    public function create()
    {
        return view('admin.permissions.create');
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('permissions.create'), 403);
        $data = $request->validate(['name' => ['required', 'string', 'unique:permissions,name']]);
        Permission::create(['name' => $data['name'], 'guard_name' => 'web']);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created.');
    }

    public function edit(Permission $permission)
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        abort_unless($request->user()->can('permissions.edit'), 403);
        $request->validate(['name' => ['required', 'string', Rule::unique('permissions', 'name')->ignore($permission->id)]]);
        $permission->update(['name' => $request->name]);

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated.');
    }

    public function destroy(Request $request, Permission $permission)
    {
        abort_unless($request->user()->can('permissions.delete'), 403);
        $permission->delete();

        return back()->with('success', 'Permission deleted.');
    }
}
