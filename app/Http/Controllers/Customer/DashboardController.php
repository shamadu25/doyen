<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        
        // Get customer's vehicles
        $vehicles = $customer->vehicles()
            ->with(['motTests' => function($query) {
                $query->latest()->limit(1);
            }])
            ->get();
        
        // Get upcoming appointments
        $upcomingAppointments = $customer->appointments()
            ->with(['vehicle', 'service'])
            ->where('appointment_date', '>=', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->limit(5)
            ->get();
        
        // Get recent job cards
        $recentJobs = $customer->jobCards()
            ->with(['vehicle', 'services', 'parts'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Get pending quotes
        $pendingQuotes = $customer->quotes()
            ->with('vehicle')
            ->whereIn('status', ['sent', 'draft'])
            ->where('valid_until', '>=', now())
            ->latest()
            ->get();
        
        // Get unpaid invoices
        $unpaidInvoices = $customer->invoices()
            ->with('vehicle')
            ->where('status', 'unpaid')
            ->latest()
            ->get();
        
        // Calculate total spent
        $totalSpent = $customer->invoices()
            ->where('status', 'paid')
            ->sum('total_amount');
        
        // Get upcoming service reminders
        $serviceReminders = collect();
        foreach ($vehicles as $vehicle) {
            $reminders = $vehicle->serviceReminders()
                ->where('next_service_date', '<=', now()->addDays(30))
                ->where('reminder_sent', false)
                ->get();
            $serviceReminders = $serviceReminders->merge($reminders);
        }
        
        return view('customer.dashboard', compact(
            'customer',
            'vehicles',
            'upcomingAppointments',
            'recentJobs',
            'pendingQuotes',
            'unpaidInvoices',
            'totalSpent',
            'serviceReminders'
        ));
    }
}
