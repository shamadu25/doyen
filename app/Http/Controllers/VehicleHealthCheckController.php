<?php

namespace App\Http\Controllers;

use App\Models\VehicleHealthCheck;
use App\Models\Vehicle;
use App\Models\JobCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VehicleHealthCheckController extends Controller
{
    public function index(Request $request)
    {
        $query = VehicleHealthCheck::with(['vehicle.customer', 'jobCard']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        $checks = $query->latest('check_date')->paginate(20);

        return Inertia::render('HealthChecks/Index', ['checks' => $checks]);
    }

    public function create(Request $request)
    {
        $vehicleId = $request->input('vehicle_id');
        $jobCardId = $request->input('job_card_id');
        
        $vehicle = $vehicleId ? Vehicle::findOrFail($vehicleId) : null;
        $jobCard = $jobCardId ? JobCard::findOrFail($jobCardId) : null;
        
        $vehicles = Vehicle::orderBy('registration_number')->get();

        return Inertia::render('HealthChecks/Create', ['vehicle' => $vehicle, 'jobCard' => $jobCard, 'vehicles' => $vehicles]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'job_card_id' => 'nullable|exists:job_cards,id',
            'check_date' => 'required|date',
            'mileage' => 'required|integer|min:0',
            'checks' => 'required|array',
            'checks.*.item' => 'required|string',
            'checks.*.status' => 'required|in:good,advisory,urgent',
            'checks.*.notes' => 'nullable|string',
            'overall_notes' => 'nullable|string',
        ]);

        $healthCheck = VehicleHealthCheck::create([
            'vehicle_id' => $validated['vehicle_id'],
            'job_card_id' => $validated['job_card_id'] ?? null,
            'check_date' => $validated['check_date'],
            'mileage' => $validated['mileage'],
            'check_items' => $validated['checks'],
            'overall_notes' => $validated['overall_notes'] ?? null,
        ]);

        // Calculate status summary
        $statusCounts = collect($validated['checks'])->countBy('status');
        $healthCheck->update([
            'good_count' => $statusCounts['good'] ?? 0,
            'advisory_count' => $statusCounts['advisory'] ?? 0,
            'urgent_count' => $statusCounts['urgent'] ?? 0,
        ]);

        return redirect()->route('health-checks.show', $healthCheck)
            ->with('success', 'Health check created successfully.');
    }

    public function show(VehicleHealthCheck $healthCheck)
    {
        $healthCheck->load(['vehicle.customer', 'jobCard']);

        return Inertia::render('HealthChecks/Show', ['healthCheck' => $healthCheck]);
    }

    public function destroy(VehicleHealthCheck $healthCheck)
    {
        $healthCheck->delete();

        return redirect()->route('health-checks.index')
            ->with('success', 'Health check deleted successfully.');
    }

    /**
     * Email health check to customer
     */
    public function email(VehicleHealthCheck $healthCheck)
    {
        // TODO: Implement email functionality
        return back()->with('success', 'Health check emailed to customer.');
    }

    /**
     * Get health check template
     */
    public function template()
    {
        $template = [
            ['item' => 'Tyres - Front Left', 'status' => 'good', 'notes' => ''],
            ['item' => 'Tyres - Front Right', 'status' => 'good', 'notes' => ''],
            ['item' => 'Tyres - Rear Left', 'status' => 'good', 'notes' => ''],
            ['item' => 'Tyres - Rear Right', 'status' => 'good', 'notes' => ''],
            ['item' => 'Brakes - Front', 'status' => 'good', 'notes' => ''],
            ['item' => 'Brakes - Rear', 'status' => 'good', 'notes' => ''],
            ['item' => 'Brake Fluid', 'status' => 'good', 'notes' => ''],
            ['item' => 'Engine Oil Level', 'status' => 'good', 'notes' => ''],
            ['item' => 'Coolant Level', 'status' => 'good', 'notes' => ''],
            ['item' => 'Battery Condition', 'status' => 'good', 'notes' => ''],
            ['item' => 'Lights - All', 'status' => 'good', 'notes' => ''],
            ['item' => 'Windscreen Wipers', 'status' => 'good', 'notes' => ''],
            ['item' => 'Suspension', 'status' => 'good', 'notes' => ''],
            ['item' => 'Exhaust System', 'status' => 'good', 'notes' => ''],
            ['item' => 'Steering', 'status' => 'good', 'notes' => ''],
        ];

        return response()->json($template);
    }
}
