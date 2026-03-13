<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $suppliers = $query->withCount('partsOrders')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'    => Supplier::count(),
            'active'   => Supplier::where('status', 'active')->count(),
            'inactive' => Supplier::where('status', 'inactive')->count(),
        ];

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'stats'     => $stats,
            'filters'   => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Suppliers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'website'        => 'nullable|url|max:255',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:2000',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount('partsOrders');
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
            'readonly' => true,
        ]);
    }

    public function edit(Supplier $supplier)
    {
        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
            'readonly' => false,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'website'        => 'nullable|url|max:255',
            'status'         => 'required|in:active,inactive',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
