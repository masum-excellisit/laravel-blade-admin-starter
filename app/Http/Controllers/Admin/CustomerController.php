<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\HandlesBulkActions;
use App\Http\Controllers\Admin\Concerns\HandlesListQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    use HandlesBulkActions, HandlesListQuery;

    public function index(Request $request)
    {
        $query = User::customers();

        if ($request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $customers = $this->applyListQuery(
            $query,
            $request,
            ['name', 'email'],
            ['name', 'email', 'status', 'created_at'],
        )->paginate(12)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function bulk(Request $request)
    {
        return $this->runBulkAction($request, User::class, 'customers', function ($query, $action, $ids) use ($request) {
            $query->customers();

            match ($action) {
                'delete' => tap($query)->get()->each(function (User $customer) use ($request) {
                    abort_unless($request->user()->can('customers.delete'), 403);
                    abort_unless($customer->isCustomer(), 404);
                    $customer->delete();
                }),
                'activate' => $this->bulkSetStatus($request, $query, 'customers', true, 'status'),
                'deactivate' => $this->bulkSetStatus($request, $query, 'customers', false, 'status'),
                default => abort(422, 'Unknown bulk action.'),
            };
        });
    }

    public function create()
    {
        return view('admin.customers.create', ['customer' => new User(['type' => User::TYPE_CUSTOMER, 'status' => true])]);
    }

    public function store(CustomerRequest $request)
    {
        abort_unless($request->user()->can('customers.create'), 403);
        $data = $request->validated();
        $data['type'] = User::TYPE_CUSTOMER;
        $data['password'] = Hash::make($data['password']);
        $data['status'] = $request->boolean('status');
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        User::create($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created.');
    }

    public function edit(User $customer)
    {
        abort_unless($customer->isCustomer(), 404);

        return view('admin.customers.edit', ['customer' => $customer]);
    }

    public function update(CustomerRequest $request, User $customer)
    {
        abort_unless($request->user()->can('customers.edit'), 403);
        abort_unless($customer->isCustomer(), 404);
        $data = $request->validated();
        $data['status'] = $request->boolean('status');
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        if ($request->hasFile('avatar')) {
            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $customer->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Request $request, User $customer)
    {
        abort_unless($request->user()->can('customers.delete'), 403);
        abort_unless($customer->isCustomer(), 404);
        $customer->delete();

        return back()->with('success', 'Customer deleted.');
    }
}
