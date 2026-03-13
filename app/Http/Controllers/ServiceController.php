<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     */
    public function index(Request $request)
    {
        $query = Service::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') $query->where('is_active', true);
            if ($request->status === 'inactive') $query->where('is_active', false);
            if ($request->status === 'approved') $query->where('is_approved', true);
            if ($request->status === 'pending') $query->where('is_approved', false);
            if ($request->status === 'website') $query->where('show_on_website', true);
        }

        $services = $query->orderBy('category')->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        $categories = Service::distinct()->pluck('category')->filter()->sort()->values();
        $stats = [
            'total'      => Service::count(),
            'active'     => Service::where('is_active', true)->count(),
            'approved'   => Service::where('is_approved', true)->count(),
            'on_website' => Service::where('show_on_website', true)->where('is_approved', true)->where('is_active', true)->count(),
        ];

        return Inertia::render('Services/Index', [
            'services'   => $services,
            'categories' => $categories,
            'stats'      => $stats,
            'filters'    => $request->only(['search', 'category', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new service
     */
    public function create()
    {
        $categories = Service::distinct()->pluck('category')->filter()->sort()->values();
        return Inertia::render('Services/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'code'                        => 'nullable|string|max:50|unique:services,code',
            'description'                 => 'nullable|string',
            'website_description'         => 'nullable|string',
            'category'                    => 'required|string|max:100',
            'default_price'               => 'required|numeric|min:0',
            'cost_price'                  => 'nullable|numeric|min:0',
            'estimated_duration_minutes'  => 'nullable|integer|min:0',
            'vat_rate'                    => 'nullable|numeric|min:0|max:100',
            'is_active'                   => 'boolean',
            'requires_booking'            => 'boolean',
            'is_approved'                 => 'boolean',
            'show_on_website'             => 'boolean',
            'icon'                        => 'nullable|string|max:100',
            'sort_order'                  => 'nullable|integer|min:0',
        ]);

        $validated['is_active']       = $request->boolean('is_active');
        $validated['requires_booking']= $request->boolean('requires_booking');
        $validated['is_approved']     = $request->boolean('is_approved');
        $validated['show_on_website'] = $request->boolean('show_on_website');
        $validated['vat_rate']        = $validated['vat_rate'] ?? 20.00;
        $validated['sort_order']      = $validated['sort_order'] ?? 0;

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            $validated['code'] = \Str::upper(\Str::slug($validated['name'], '_'));
        }

        Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', 'Service created successfully.');
    }

    /**
     * Display the specified service
     */
    public function show(Service $service)
    {
        $categories = Service::distinct()->pluck('category')->filter()->sort()->values();
        return Inertia::render('Services/Edit', [
            'service'    => $service,
            'categories' => $categories,
            'readonly'   => true,
        ]);
    }

    /**
     * Show the form for editing the specified service
     */
    public function edit(Service $service)
    {
        $categories = Service::distinct()->pluck('category')->filter()->sort()->values();
        return Inertia::render('Services/Edit', [
            'service'    => $service,
            'categories' => $categories,
            'readonly'   => false,
        ]);
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'                        => 'required|string|max:255',
            'code'                        => 'nullable|string|max:50|unique:services,code,' . $service->id,
            'description'                 => 'nullable|string',
            'website_description'         => 'nullable|string',
            'category'                    => 'required|string|max:100',
            'default_price'               => 'required|numeric|min:0',
            'cost_price'                  => 'nullable|numeric|min:0',
            'estimated_duration_minutes'  => 'nullable|integer|min:0',
            'vat_rate'                    => 'nullable|numeric|min:0|max:100',
            'is_active'                   => 'boolean',
            'requires_booking'            => 'boolean',
            'is_approved'                 => 'boolean',
            'show_on_website'             => 'boolean',
            'icon'                        => 'nullable|string|max:100',
            'sort_order'                  => 'nullable|integer|min:0',
        ]);

        $validated['is_active']       = $request->boolean('is_active');
        $validated['requires_booking']= $request->boolean('requires_booking');
        $validated['is_approved']     = $request->boolean('is_approved');
        $validated['show_on_website'] = $request->boolean('show_on_website');

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', 'Service updated successfully.');
    }

    /**
     * Remove the specified service
     */
    public function destroy(Service $service)
    {
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service deleted successfully.');
    }

    /**
     * Toggle approved status
     */
    public function toggleApprove(Service $service)
    {
        $service->update(['is_approved' => !$service->is_approved]);
        $status = $service->is_approved ? 'approved' : 'unapproved';
        return back()->with('success', "Service \"{$service->name}\" has been {$status}.");
    }

    /**
     * Toggle show on website
     */
    public function toggleWebsite(Service $service)
    {
        // Can only show on website if approved and active
        if (!$service->is_approved && !$service->show_on_website) {
            return back()->with('error', 'Service must be approved before it can be shown on the website.');
        }
        $service->update(['show_on_website' => !$service->show_on_website]);
        $status = $service->show_on_website ? 'now visible on website' : 'hidden from website';
        return back()->with('success', "Service \"{$service->name}\" is {$status}.");
    }

    /**
     * Toggle active status — disabling also hides from website; re-enabling restores website visibility
     */
    public function toggleActive(Service $service)
    {
        if ($service->is_active) {
            // Disabling: hide from website too
            $service->update(['is_active' => false, 'show_on_website' => false]);
            return back()->with('success', "Service \"{$service->name}\" has been disabled and hidden from the website.");
        } else {
            // Re-enabling: make active, approved and visible on website
            $service->update(['is_active' => true, 'is_approved' => true, 'show_on_website' => true]);
            return back()->with('success', "Service \"{$service->name}\" has been enabled and is now live on the website.");
        }
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action'      => 'required|in:approve,unapprove,enable,disable,show_website,hide_website,delete',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:services,id',
        ]);

        $services = Service::whereIn('id', $request->service_ids);
        $count    = $services->count();

        switch ($request->action) {
            case 'approve':
                $services->update(['is_approved' => true]);
                return back()->with('success', "{$count} service(s) approved.");
            case 'unapprove':
                $services->update(['is_approved' => false, 'show_on_website' => false]);
                return back()->with('success', "{$count} service(s) unapproved.");
            case 'enable':
                $services->update(['is_active' => true]);
                return back()->with('success', "{$count} service(s) enabled.");
            case 'disable':
                $services->update(['is_active' => false, 'show_on_website' => false]);
                return back()->with('success', "{$count} service(s) disabled.");
            case 'show_website':
                $services->where('is_approved', true)->where('is_active', true)->update(['show_on_website' => true]);
                return back()->with('success', "{$count} service(s) published to website (approved & active ones only).");
            case 'hide_website':
                $services->update(['show_on_website' => false]);
                return back()->with('success', "{$count} service(s) hidden from website.");
            case 'delete':
                $services->delete();
                return back()->with('success', "{$count} service(s) deleted.");
        }

        return back()->with('error', 'Unknown action.');
    }

    /**
     * Bulk update prices
     */
    public function bulkUpdatePrices(Request $request)
    {
        $request->validate([
            'adjustment_type'  => 'required|in:percentage,fixed',
            'adjustment_value' => 'required|numeric',
            'service_ids'      => 'required|array',
            'service_ids.*'    => 'exists:services,id',
        ]);

        $services = Service::whereIn('id', $request->service_ids)->get();

        foreach ($services as $service) {
            if ($request->adjustment_type === 'percentage') {
                $newPrice = $service->default_price * (1 + ($request->adjustment_value / 100));
            } else {
                $newPrice = $service->default_price + $request->adjustment_value;
            }
            $service->update(['default_price' => max(0, $newPrice)]);
        }

        return redirect()->route('services.index')
            ->with('success', count($services) . ' service(s) price updated.');
    }

    /**
     * Get service pricing for quick lookup (API)
     */
    public function getPricing(Service $service)
    {
        return response()->json([
            'id'                          => $service->id,
            'name'                        => $service->name,
            'default_price'               => $service->default_price,
            'vat_rate'                    => $service->vat_rate,
            'price_with_vat'              => $service->default_price * (1 + ($service->vat_rate / 100)),
            'estimated_duration_minutes'  => $service->estimated_duration_minutes,
        ]);
    }
}

