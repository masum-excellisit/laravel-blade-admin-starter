<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $roles = $this->applyListQuery(
            Role::withCount('users', 'permissions'),
            $request,
            ['name'],
            ['name', 'created_at'],
        )->paginate(12)->withQueryString();

        return view('admin.roles.index', compact('roles'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, Role::class, 'roles', function ($query, $action, $ids) use ($request) {
            match ($action) {
                'delete' => tap($query->where('name', '!=', 'super-admin'))->get()->each(function (Role $role) use ($request) {
                    abort_unless($request->user()->can('roles.delete'), 403);
                    $role->delete();
                }),
                default => abort(422, 'Unknown bulk action.'),
            };
        });
    }

    public function create()
    {
        return view('admin.roles.create', ['permissions' => $this->grouped(), 'role' => new Role]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->can('roles.create'), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'permissions' => ['array'],
        ]);
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', ['role' => $role, 'permissions' => $this->grouped(), 'assigned' => $role->permissions->pluck('name')->toArray()]);
    }

    public function update(Request $request, Role $role)
    {
        abort_unless($request->user()->can('roles.edit'), 403);
        $request->validate([
            'name' => ['required', 'string', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
        ]);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role)
    {
        abort_unless($request->user()->can('roles.delete'), 403);
        abort_if($role->name === 'super-admin', 403, 'The super-admin role is protected.');
        $role->delete();

        return back()->with('success', 'Role deleted.');
    }

    protected function grouped()
    {
        return Permission::orderBy('name')->get()->groupBy(fn ($p) => explode('.', $p->name)[0]);
    }
}
