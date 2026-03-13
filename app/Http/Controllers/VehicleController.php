<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::query()
            ->with('customer')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('registration_number', 'like', "%{$search}%")
                      ->orWhere('make', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('vin', 'like', "%{$search}%");
                });
            })
            ->when($request->mot_due, function ($query) {
                $query->whereNotNull('mot_due_date')->where('mot_due_date', '<=', now()->addDays(30));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => $request->only('search', 'mot_due'),
        ]);
    }

    public function create(Request $request)
    {
        $customers = Customer::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        return Inertia::render('Vehicles/Create', [
            'customers' => $customers,
            'preselectedCustomerId' => $request->customer_id,
        ]);
    }

    public function store(VehicleRequest $request)
    {
        $vehicle = Vehicle::create($request->validated());
        ActivityLog::log('created', "Vehicle {$vehicle->registration_number} created", $vehicle);
        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Vehicle added successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'customer',
            'motTests' => fn($q) => $q->latest(),
            'appointments' => fn($q) => $q->latest()->take(10),
            'jobCards' => fn($q) => $q->latest()->take(10)->with('assignedTo'),
            'invoices' => fn($q) => $q->latest()->take(10),
        ]);

        return Inertia::render('Vehicles/Show', ['vehicle' => $vehicle]);
    }

    public function edit(Vehicle $vehicle)
    {
        $customers = Customer::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        return Inertia::render('Vehicles/Edit', [
            'vehicle' => $vehicle,
            'customers' => $customers,
        ]);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());
        ActivityLog::log('updated', "Vehicle {$vehicle->registration_number} updated", $vehicle);
        return redirect()->route('vehicles.show', $vehicle)->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        ActivityLog::log('deleted', "Vehicle {$vehicle->registration_number} deleted", $vehicle);
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
