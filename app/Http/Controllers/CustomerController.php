<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->withCount('vehicles')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Customers/Create');
    }

    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        ActivityLog::log('created', "Customer {$customer->full_name} created", $customer);
        return redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['vehicles.motTests', 'invoices' => fn($q) => $q->latest()->take(10), 'appointments' => fn($q) => $q->latest()->take(10)]);
        return Inertia::render('Customers/Show', ['customer' => $customer]);
    }

    public function edit(Customer $customer)
    {
        return Inertia::render('Customers/Edit', ['customer' => $customer]);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        ActivityLog::log('updated', "Customer {$customer->full_name} updated", $customer);
        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        ActivityLog::log('deleted', "Customer {$customer->full_name} deleted", $customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
