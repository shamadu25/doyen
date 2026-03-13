<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartRequest;
use App\Models\ActivityLog;
use App\Models\InventoryTransaction;
use App\Models\Part;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartController extends Controller
{
    public function index(Request $request)
    {
        $parts = Part::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('part_number', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('manufacturer', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->category, fn($q, $c) => $q->where('category', $c))
            ->when($request->low_stock, fn($q) => $q->whereColumn('stock_quantity', '<=', 'minimum_stock'))
            ->where('is_active', true)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Part::distinct()->pluck('category')->filter();
        $lowStockCount = Part::whereColumn('stock_quantity', '<=', 'minimum_stock')->where('is_active', true)->count();

        return Inertia::render('Inventory/Index', [
            'parts' => $parts,
            'filters' => $request->only('search', 'category', 'low_stock'),
            'categories' => $categories,
            'lowStockCount' => $lowStockCount,
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventory/Create');
    }

    public function store(PartRequest $request)
    {
        $part = Part::create(array_merge($request->validated(), ['is_active' => true]));

        if ($part->stock_quantity > 0) {
            InventoryTransaction::recordIn($part, $part->stock_quantity, 'Initial stock');
        }

        ActivityLog::log('created', "Part {$part->part_number} - {$part->name} added", $part);
        return redirect()->route('inventory.show', $part)->with('success', 'Part added to inventory.');
    }

    public function show(Part $part)
    {
        $transactions = InventoryTransaction::where('part_id', $part->id)
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        return Inertia::render('Inventory/Show', [
            'part' => $part,
            'transactions' => $transactions,
            'profitMargin' => $part->selling_price > 0
                ? round((($part->selling_price - $part->cost_price) / $part->selling_price) * 100, 1)
                : 0,
        ]);
    }

    public function edit(Part $part)
    {
        return Inertia::render('Inventory/Edit', ['part' => $part]);
    }

    public function update(PartRequest $request, Part $part)
    {
        $part->update($request->validated());
        return redirect()->route('inventory.show', $part)->with('success', 'Part updated.');
    }

    public function destroy(Part $part)
    {
        $part->update(['is_active' => false]);
        return redirect()->route('inventory.index')->with('success', 'Part deactivated.');
    }

    public function adjustStock(Request $request, Part $part)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out,adjustment',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['type'] === 'in') {
            InventoryTransaction::recordIn($part, $validated['quantity'], $validated['notes']);
        } elseif ($validated['type'] === 'out') {
            InventoryTransaction::recordOut($part, $validated['quantity'], $validated['notes']);
        } else {
            $diff = $validated['quantity'] - $part->stock_quantity;
            $part->update(['stock_quantity' => $validated['quantity']]);
            InventoryTransaction::create([
                'part_id' => $part->id,
                'type' => 'adjustment',
                'quantity' => $diff,
                'notes' => $validated['notes'] ?? 'Stock adjustment',
                'user_id' => auth()->id(),
            ]);
        }

        return back()->with('success', 'Stock adjusted.');
    }
}
