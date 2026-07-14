<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = User::customers()
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%")))
            ->when($request->status !== null && $request->status !== '', fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.customers.index', compact('customers'));
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
