<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Part;
use App\Models\Payment;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $dailyRevenue = Payment::whereDate('payment_date', $today)->where('status', 'completed')->sum('amount');
        $monthlyRevenue = Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->where('status', 'completed')->sum('amount');

        $motRevenue = Invoice::whereHas('jobCard', fn($q) => $q->whereHas('appointment', fn($q2) => $q2->where('appointment_type', 'mot')))
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_amount');

        $outstandingInvoices = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])->count();
        $outstandingAmount = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')->value('total');

        $jobsInProgress = JobCard::whereIn('status', ['pending', 'in_progress', 'awaiting_parts'])->count();
        $jobsCompletedToday = JobCard::whereDate('completion_date', $today)->count();
        $bookingsToday = Appointment::whereDate('scheduled_date', $today)->count();
        $bookingsThisWeek = Appointment::whereBetween('scheduled_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $lowStockCount = Part::whereColumn('stock_quantity', '<=', 'minimum_stock')->where('is_active', true)->count();

        $revenueChart = $this->getRevenueChart(30);

        $recentJobs = JobCard::with(['customer', 'vehicle', 'assignedTo'])->latest()->take(5)->get();
        $recentBookings = Appointment::with(['customer', 'vehicle'])->latest()->take(5)->get();
        $upcomingMots = Vehicle::whereNotNull('mot_due_date')
            ->where('mot_due_date', '<=', Carbon::now()->addDays(30))
            ->with('customer')->orderBy('mot_due_date')->take(10)->get();

        return Inertia::render('Dashboard', [
            'stats' => [
                'daily_revenue' => round((float) $dailyRevenue, 2),
                'monthly_revenue' => round((float) $monthlyRevenue, 2),
                'mot_revenue' => round((float) $motRevenue, 2),
                'outstanding_invoices' => $outstandingInvoices,
                'outstanding_amount' => round((float) $outstandingAmount, 2),
                'jobs_in_progress' => $jobsInProgress,
                'jobs_completed_today' => $jobsCompletedToday,
                'bookings_today' => $bookingsToday,
                'bookings_this_week' => $bookingsThisWeek,
                'low_stock_count' => $lowStockCount,
                'customers_total' => Customer::count(),
                'vehicles_total' => Vehicle::count(),
                'labour_revenue' => 0,
                'parts_revenue' => 0,
            ],
            'revenueChart' => $revenueChart,
            'recentJobs' => $recentJobs,
            'recentBookings' => $recentBookings,
            'upcomingMots' => $upcomingMots,
        ]);
    }

    private function getRevenueChart(int $days): array
    {
        $labels = [];
        $revenue = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');
            $revenue[] = round((float) Payment::whereDate('payment_date', $date)->where('status', 'completed')->sum('amount'), 2);
        }
        return compact('labels', 'revenue');
    }
}
