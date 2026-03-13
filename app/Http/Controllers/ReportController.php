<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\MotTest;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '30');
        
        // Convert period to days
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => (int) $period,
        };
        
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        // Revenue data
        $totalRevenue = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $previousPeriodRevenue = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$startDate->copy()->subDays($days), $startDate])
            ->sum('amount');

        $revenueGrowth = $previousPeriodRevenue > 0
            ? round((($totalRevenue - $previousPeriodRevenue) / $previousPeriodRevenue) * 100, 1)
            : 0;

        // Jobs completed
        $jobsCompleted = JobCard::where('status', 'completed')
            ->whereBetween('completion_date', [$startDate, $endDate])
            ->count();

        // MOT stats
        $motTests = MotTest::whereBetween('test_date', [$startDate, $endDate])->count();
        $motPassRate = $motTests > 0
            ? round((MotTest::where('test_result', 'passed')->whereBetween('test_date', [$startDate, $endDate])->count() / $motTests) * 100, 1)
            : 0;

        // Outstanding
        $outstandingAmount = Invoice::whereIn('status', ['sent', 'partial', 'overdue'])
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as total')
            ->value('total');

        // Total customers
        $totalCustomers = DB::table('customers')->count();

        // Average job value
        $avgJobValue = $jobsCompleted > 0 
            ? round((float) ($totalRevenue / $jobsCompleted), 2)
            : 0;

        // Revenue chart
        $chartLabels = [];
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('M d');
            $chartData[] = round((float) Payment::whereDate('payment_date', $date)->where('status', 'completed')->sum('amount'), 2);
        }

        // Top services
        $topServices = DB::table('invoice_items')
            ->select('description as name', DB::raw('SUM(quantity * unit_price) as revenue'), DB::raw('COUNT(*) as count'))
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->whereBetween('invoices.created_at', [$startDate, $endDate])
            ->groupBy('description')
            ->orderByDesc('revenue')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'count' => (int) $item->count,
                    'revenue' => (float) $item->revenue,
                ];
            });

        // Technician productivity
        $techProductivity = DB::table('job_cards')
            ->join('users', 'users.id', '=', 'job_cards.assigned_to')
            ->select(
                'users.name', 
                DB::raw('COUNT(*) as jobs'),
                DB::raw('COALESCE(SUM(TIMESTAMPDIFF(HOUR, job_cards.created_at, job_cards.completion_date)), 0) as hours')
            )
            ->where('job_cards.status', 'completed')
            ->whereBetween('job_cards.completion_date', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('jobs')
            ->take(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'jobs' => (int) $item->jobs,
                    'hours' => (int) $item->hours,
                    'revenue' => 0, // Would need to join invoices to get actual revenue
                ];
            });

        return Inertia::render('Reports/Index', [
            'period' => $period,
            'stats' => [
                'totalRevenue' => round((float) $totalRevenue, 2),
                'totalJobs' => $jobsCompleted,
                'totalCustomers' => $totalCustomers,
                'avgJobValue' => $avgJobValue,
                'outstandingBalance' => round((float) $outstandingAmount, 2),
                'motTests' => $motTests,
            ],
            'revenueChart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
            ],
            'topServices' => $topServices,
            'techProductivity' => $techProductivity,
        ]);
    }

    public function export(Request $request)
    {
        $period = $request->get('period', '30');
        
        // Convert period to days
        $days = match($period) {
            'week' => 7,
            'month' => 30,
            'quarter' => 90,
            'year' => 365,
            default => (int) $period,
        };
        
        $startDate = Carbon::now()->subDays($days);

        $payments = Payment::with(['invoice', 'customer'])
            ->where('status', 'completed')
            ->whereBetween('payment_date', [$startDate, now()])
            ->get();

        $csv = "Date,Invoice,Customer,Amount,Method,Reference\n";
        foreach ($payments as $p) {
            $csv .= implode(',', [
                $p->payment_date?->format('Y-m-d'),
                $p->invoice?->invoice_number ?? 'N/A',
                '"' . ($p->customer?->full_name ?? 'N/A') . '"',
                $p->amount,
                $p->payment_method,
                $p->payment_reference ?? '',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="revenue-report.csv"',
        ]);
    }
}
