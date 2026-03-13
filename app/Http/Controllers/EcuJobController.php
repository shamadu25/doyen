<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\EcuJob;
use App\Models\JobCard;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EcuJobController extends Controller
{
    public function index(Request $request)
    {
        $query = EcuJob::with(['vehicle', 'customer', 'technician']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $s = $request->search;
                $q->where('job_number', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($c) => $c->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"))
                  ->orWhereHas('vehicle', fn($v) => $v->where('registration_number', 'like', "%{$s}%")->orWhere('make', 'like', "%{$s}%")->orWhere('model', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('technician')) {
            $query->where('technician_id', $request->technician);
        }

        $jobs = $query->latest('date_in')->paginate(20)->withQueryString();

        $stats = [
            'total'       => EcuJob::count(),
            'in_progress' => EcuJob::where('status', 'in_progress')->count(),
            'completed'   => EcuJob::where('status', 'completed')->count(),
            'booked'      => EcuJob::where('status', 'booked')->count(),
        ];

        // Count by category
        $categoryCounts = EcuJob::selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return Inertia::render('EcuJobs/Index', [
            'jobs'           => $jobs,
            'stats'          => $stats,
            'categoryCounts' => $categoryCounts,
            'categories'     => EcuJob::categories(),
            'statusLabels'   => EcuJob::statusLabels(),
            'technicians'    => User::where('is_active', true)->get(['id', 'name']),
            'filters'        => $request->only(['search', 'category', 'status', 'technician']),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('EcuJobs/Create', [
            'customers'    => Customer::select('id', 'first_name', 'last_name', 'phone', 'email')->orderBy('first_name')->get(),
            'vehicles'     => Vehicle::select('id', 'customer_id', 'registration_number', 'make', 'model', 'year')->get(),
            'technicians'  => User::where('is_active', true)->get(['id', 'name']),
            'categories'   => EcuJob::categories(),
            'serviceTypes' => EcuJob::serviceTypes(),
            'preVehicleId' => $request->vehicle_id,
            'preCustomerId'=> $request->customer_id,
            'preJobCardId' => $request->job_card_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id'           => 'required|exists:vehicles,id',
            'customer_id'          => 'required|exists:customers,id',
            'job_card_id'          => 'nullable|exists:job_cards,id',
            'technician_id'        => 'nullable|exists:users,id',
            'category'             => 'required|in:diagnostics,remapping,airbag_srs,emissions,immobiliser,mileage_correction,electrical,other',
            'service_type'         => 'required|string|max:100',
            'service_label'        => 'nullable|string|max:255',
            'status'               => 'required|in:booked,in_progress,completed,on_hold,cancelled',
            'date_in'              => 'required|date',
            'date_completed'       => 'nullable|date|after_or_equal:date_in',
            'mileage'              => 'nullable|integer|min:0',
            'ecu_part_number'      => 'nullable|string|max:100',
            'ecu_software_version' => 'nullable|string|max:100',
            'ecu_hardware_version' => 'nullable|string|max:100',
            'immo_ref'             => 'nullable|string|max:100',
            'fault_codes_found'    => 'nullable|array',
            'fault_codes_found.*'  => 'string|max:50',
            'fault_codes_cleared'  => 'nullable|array',
            'fault_codes_cleared.*'=> 'string|max:50',
            'all_codes_cleared'    => 'boolean',
            'work_required'        => 'nullable|string|max:3000',
            'work_performed'       => 'nullable|string|max:3000',
            'pre_condition'        => 'nullable|string|max:1000',
            'post_condition'       => 'nullable|string|max:1000',
            'internal_notes'       => 'nullable|string|max:2000',
            'details'              => 'nullable|array',
            'price'                => 'nullable|numeric|min:0',
            'warranty_months'      => 'nullable|integer|min:0',
            'customer_notified'    => 'boolean',
        ]);

        $validated['all_codes_cleared']  = $request->boolean('all_codes_cleared');
        $validated['customer_notified']  = $request->boolean('customer_notified');
        $validated['price']              = $validated['price'] ?? 0;
        $validated['warranty_months']    = $validated['warranty_months'] ?? 0;
        $validated['is_invoiced']        = false;

        EcuJob::create($validated);

        return redirect()->route('ecu-jobs.index')
            ->with('success', 'ECU job created successfully.');
    }

    public function show(EcuJob $ecuJob)
    {
        $ecuJob->load(['vehicle', 'customer', 'technician', 'jobCard']);
        return Inertia::render('EcuJobs/Edit', [
            'ecuJob'       => $ecuJob,
            'technicians'  => User::where('is_active', true)->get(['id', 'name']),
            'categories'   => EcuJob::categories(),
            'serviceTypes' => EcuJob::serviceTypes(),
            'readonly'     => true,
        ]);
    }

    public function edit(EcuJob $ecuJob)
    {
        $ecuJob->load(['vehicle', 'customer', 'technician', 'jobCard']);
        return Inertia::render('EcuJobs/Edit', [
            'ecuJob'       => $ecuJob,
            'technicians'  => User::where('is_active', true)->get(['id', 'name']),
            'categories'   => EcuJob::categories(),
            'serviceTypes' => EcuJob::serviceTypes(),
            'readonly'     => false,
        ]);
    }

    public function update(Request $request, EcuJob $ecuJob)
    {
        $validated = $request->validate([
            'technician_id'        => 'nullable|exists:users,id',
            'category'             => 'required|in:diagnostics,remapping,airbag_srs,emissions,immobiliser,mileage_correction,electrical,other',
            'service_type'         => 'required|string|max:100',
            'service_label'        => 'nullable|string|max:255',
            'status'               => 'required|in:booked,in_progress,completed,on_hold,cancelled',
            'date_in'              => 'required|date',
            'date_completed'       => 'nullable|date|after_or_equal:date_in',
            'mileage'              => 'nullable|integer|min:0',
            'ecu_part_number'      => 'nullable|string|max:100',
            'ecu_software_version' => 'nullable|string|max:100',
            'ecu_hardware_version' => 'nullable|string|max:100',
            'immo_ref'             => 'nullable|string|max:100',
            'fault_codes_found'    => 'nullable|array',
            'fault_codes_found.*'  => 'string|max:50',
            'fault_codes_cleared'  => 'nullable|array',
            'fault_codes_cleared.*'=> 'string|max:50',
            'all_codes_cleared'    => 'boolean',
            'work_required'        => 'nullable|string|max:3000',
            'work_performed'       => 'nullable|string|max:3000',
            'pre_condition'        => 'nullable|string|max:1000',
            'post_condition'       => 'nullable|string|max:1000',
            'internal_notes'       => 'nullable|string|max:2000',
            'details'              => 'nullable|array',
            'price'                => 'nullable|numeric|min:0',
            'warranty_months'      => 'nullable|integer|min:0',
            'customer_notified'    => 'boolean',
        ]);

        $validated['all_codes_cleared'] = $request->boolean('all_codes_cleared');
        $validated['customer_notified'] = $request->boolean('customer_notified');

        // Auto-set date_completed when status changed to completed
        if ($validated['status'] === 'completed' && !$ecuJob->date_completed) {
            $validated['date_completed'] = $validated['date_completed'] ?? now()->toDateString();
        }

        $ecuJob->update($validated);

        return redirect()->route('ecu-jobs.index')
            ->with('success', 'ECU job updated successfully.');
    }

    public function destroy(EcuJob $ecuJob)
    {
        $ecuJob->delete();
        return redirect()->route('ecu-jobs.index')
            ->with('success', 'ECU job deleted.');
    }
}
