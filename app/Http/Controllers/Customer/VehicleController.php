<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        $vehicles = $customer->vehicles()
            ->with(['motTests' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get();
        
        return view('customer.vehicles.index', compact('vehicles'));
    }

    public function show(Vehicle $vehicle)
    {
        $customer = Auth::guard('customer')->user();
        
        if ($vehicle->customer_id !== $customer->id) {
            abort(403);
        }

        // Load relationships
        $vehicle->load([
            'motTests' => function($query) {
                $query->latest()->limit(5);
            },
            'jobCards' => function($query) {
                $query->with(['services', 'parts'])->latest()->limit(10);
            },
            'healthChecks' => function($query) {
                $query->latest()->limit(5);
            },
            'documents',
            'serviceReminders'
        ]);

        // Get service history
        $serviceHistory = $vehicle->jobCards()
            ->with(['services', 'parts'])
            ->where('status', 'completed')
            ->latest()
            ->get();

        // Calculate total spent on this vehicle
        $totalSpent = $vehicle->jobCards()
            ->where('status', 'completed')
            ->sum('total_amount');

        return view('customer.vehicles.show', compact('vehicle', 'serviceHistory', 'totalSpent'));
    }
}
