<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $users = $this->applyListQuery(
            User::admins()->with('roles'),
            $request,
            ['name', 'email'],
            ['name', 'email', 'status', 'last_login_at', 'created_at'],
        )->paginate(12)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, User::class, 'users', function ($query, $action, $ids) use ($request) {
            $query->admins();

            match ($action) {
                'delete' => tap($query->where('id', '!=', $request->user()->id))->get()->each(function (User $user) use ($request) {
                    abort_unless($request->user()->can('users.delete'), 403);
                    $user->delete();
                }),
                'activate' => $this->bulkSetStatus($request, $query, 'users', true, 'status'),
                'deactivate' => $this->bulkSetStatus($request, $query, 'users', false, 'status'),
                default => abort(422, 'Unknown bulk action.'),
            };
        });
    }

    public function create()
    {
        return view('admin.users.create', ['user' => new User, 'roles' => Role::pluck('name')]);
    }

    public function store(UserRequest $request)
    {
        abort_unless($request->user()->can('users.create'), 403);
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['status'] = $request->boolean('status');
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user = User::create($data);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user->load('roles'), 'roles' => Role::pluck('name')]);
    }

    public function update(UserRequest $request, User $user)
    {
        abort_unless($request->user()->can('users.edit'), 403);
        $data = $request->validated();
        $data['status'] = $request->boolean('status');
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);
        $user->syncRoles($request->input('roles', []));

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($request->user()->can('users.delete'), 403);
        abort_if($user->id === $request->user()->id, 403, 'You cannot delete yourself.');
        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
