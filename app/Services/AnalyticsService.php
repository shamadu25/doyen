<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\JobCard;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Service;
use App\Models\Part;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Analytics Service
 * 
 * Provides comprehensive business analytics and reporting:
 * - Revenue analysis
 * - Customer analytics
 * - Service popularity
 * - Technician performance
 * - Profitability metrics
 */
class AnalyticsService
{
    /**
     * Get revenue report for a period
     */
    public function getRevenueReport($startDate, $endDate, $groupBy = 'day')
    {
        $invoices = Invoice::where('status', 'paid')
            ->whereBetween('paid_date', [$startDate, $endDate])
            ->select([
                DB::raw("DATE({$this->getDateFormat($groupBy)}) as period"),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('SUM(subtotal) as subtotal'),
                DB::raw('SUM(vat_amount) as vat'),
                DB::raw('SUM(total_amount) as total'),
                DB::raw('AVG(total_amount) as average_value'),
            ])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'period' => ['start' => $startDate, 'end' => $endDate],
            'data' => $invoices,
            'summary' => [
                'total_revenue' => $invoices->sum('total'),
                'total_invoices' => $invoices->sum('invoice_count'),
                'average_invoice' => $invoices->avg('average_value'),
                'total_vat' => $invoices->sum('vat'),
            ],
        ];
    }

    /**
     * Get customer analytics
     */
    public function getCustomerAnalytics($startDate, $endDate)
    {
        $topCustomers = Customer::select([
                'customers.id',
                'customers.name',
                'customers.email',
                'customers.phone',
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('SUM(invoices.total_amount) as total_spent'),
                DB::raw('AVG(invoices.total_amount) as average_invoice'),
            ])
            ->join('invoices', 'customers.id', '=', 'invoices.customer_id')
            ->where('invoices.status', 'paid')
            ->whereBetween('invoices.paid_date', [$startDate, $endDate])
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.phone')
            ->orderByDesc('total_spent')
            ->limit(20)
            ->get();

        $newCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        
        $activeCustomers = Customer::whereHas('invoices', function($query) use ($startDate, $endDate) {
            $query->where('status', 'paid')
                  ->whereBetween('paid_date', [$startDate, $endDate]);
        })->count();

        return [
            'top_customers' => $topCustomers,
            'new_customers' => $newCustomers,
            'active_customers' => $activeCustomers,
            'total_customers' => Customer::count(),
            'retention_rate' => Customer::count() > 0 
                ? round(($activeCustomers / Customer::count()) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Get popular services
     */
    public function getPopularServices($startDate, $endDate)
    {
        return DB::table('job_card_services')
            ->join('services', 'job_card_services.service_id', '=', 'services.id')
            ->join('job_cards', 'job_card_services.job_card_id', '=', 'job_cards.id')
            ->whereBetween('job_cards.date_in', [$startDate, $endDate])
            ->select([
                'services.id',
                'services.name',
                'services.category',
                DB::raw('COUNT(*) as times_ordered'),
                DB::raw('SUM(job_card_services.quantity) as total_quantity'),
                DB::raw('SUM(job_card_services.price * job_card_services.quantity) as total_revenue'),
                DB::raw('AVG(job_card_services.price) as average_price'),
            ])
            ->groupBy('services.id', 'services.name', 'services.category')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();
    }

    /**
     * Get parts analytics
     */
    public function getPartsAnalytics($startDate, $endDate)
    {
        $topParts = DB::table('job_card_parts')
            ->join('parts', 'job_card_parts.part_id', '=', 'parts.id')
            ->join('job_cards', 'job_card_parts.job_card_id', '=', 'job_cards.id')
            ->whereBetween('job_cards.date_in', [$startDate, $endDate])
            ->select([
                'parts.id',
                'parts.part_number',
                'parts.name',
                'parts.category',
                DB::raw('COUNT(*) as times_used'),
                DB::raw('SUM(job_card_parts.quantity) as total_quantity'),
                DB::raw('SUM(job_card_parts.selling_price * job_card_parts.quantity) as total_revenue'),
                DB::raw('SUM(job_card_parts.cost_price * job_card_parts.quantity) as total_cost'),
                DB::raw('SUM((job_card_parts.selling_price - job_card_parts.cost_price) * job_card_parts.quantity) as profit'),
            ])
            ->groupBy('parts.id', 'parts.part_number', 'parts.name', 'parts.category')
            ->orderByDesc('profit')
            ->limit(20)
            ->get();

        $lowStock = Part::whereRaw('stock_quantity <= minimum_stock')
            ->where('is_active', true)
            ->orderBy('stock_quantity')
            ->limit(20)
            ->get();

        return [
            'top_parts' => $topParts,
            'low_stock' => $lowStock,
            'total_parts_value' => Part::sum(DB::raw('stock_quantity * cost_price')),
        ];
    }

    /**
     * Get appointment statistics
     */
    public function getAppointmentStats($startDate, $endDate)
    {
        $appointments = Appointment::whereBetween('scheduled_date', [$startDate, $endDate])->get();

        $statusBreakdown = $appointments->groupBy('status')->map(function($group) {
            return $group->count();
        });

        $conversionRate = $appointments->count() > 0
            ? round(($appointments->where('status', 'completed')->count() / $appointments->count()) * 100, 2)
            : 0;

        return [
            'total_appointments' => $appointments->count(),
            'status_breakdown' => $statusBreakdown,
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Get profitability analysis
     */
    public function getProfitabilityAnalysis($startDate, $endDate)
    {
        // Services profitability
        $servicesProfit = DB::table('job_card_services')
            ->join('job_cards', 'job_card_services.job_card_id', '=', 'job_cards.id')
            ->whereBetween('job_cards.date_in', [$startDate, $endDate])
            ->select([
                DB::raw('SUM(job_card_services.price * job_card_services.quantity) as revenue'),
                DB::raw('SUM(job_card_services.cost * job_card_services.quantity) as cost'),
            ])
            ->first();

        // Parts profitability
        $partsProfit = DB::table('job_card_parts')
            ->join('job_cards', 'job_card_parts.job_card_id', '=', 'job_cards.id')
            ->whereBetween('job_cards.date_in', [$startDate, $endDate])
            ->select([
                DB::raw('SUM(job_card_parts.selling_price * job_card_parts.quantity) as revenue'),
                DB::raw('SUM(job_card_parts.cost_price * job_card_parts.quantity) as cost'),
            ])
            ->first();

        $totalRevenue = ($servicesProfit->revenue ?? 0) + ($partsProfit->revenue ?? 0);
        $totalCost = ($servicesProfit->cost ?? 0) + ($partsProfit->cost ?? 0);
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;

        return [
            'services' => [
                'revenue' => $servicesProfit->revenue ?? 0,
                'cost' => $servicesProfit->cost ?? 0,
                'profit' => ($servicesProfit->revenue ?? 0) - ($servicesProfit->cost ?? 0),
            ],
            'parts' => [
                'revenue' => $partsProfit->revenue ?? 0,
                'cost' => $partsProfit->cost ?? 0,
                'profit' => ($partsProfit->revenue ?? 0) - ($partsProfit->cost ?? 0),
            ],
            'total' => [
                'revenue' => $totalRevenue,
                'cost' => $totalCost,
                'profit' => $totalProfit,
                'margin_percent' => $profitMargin,
            ],
        ];
    }

    /**
     * Get dashboard summary
     */
    public function getDashboardSummary()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'today' => [
                'appointments' => Appointment::whereDate('scheduled_date', $today)->count(),
                'completed_jobs' => JobCard::whereDate('date_completed', $today)->count(),
                'revenue' => Invoice::where('status', 'paid')
                    ->whereDate('paid_date', $today)
                    ->sum('total_amount'),
            ],
            'this_month' => [
                'revenue' => Invoice::where('status', 'paid')
                    ->where('paid_date', '>=', $thisMonth)
                    ->sum('total_amount'),
                'invoices' => Invoice::where('invoice_date', '>=', $thisMonth)->count(),
                'customers' => Customer::where('created_at', '>=', $thisMonth)->count(),
            ],
            'last_month' => [
                'revenue' => Invoice::where('status', 'paid')
                    ->whereBetween('paid_date', [$lastMonth, $thisMonth])
                    ->sum('total_amount'),
            ],
            'pending' => [
                'appointments' => Appointment::where('status', 'confirmed')
                    ->where('scheduled_date', '>=', $today)
                    ->count(),
                'job_cards' => JobCard::whereIn('status', ['open', 'in_progress'])->count(),
                'invoices_value' => Invoice::whereIn('status', ['sent', 'partially_paid'])
                    ->sum('total_amount'),
            ],
        ];
    }

    /**
     * Get date format for grouping
     */
    protected function getDateFormat($groupBy)
    {
        switch ($groupBy) {
            case 'hour':
                return 'paid_date';
            case 'day':
                return 'paid_date';
            case 'week':
                return 'paid_date';
            case 'month':
                return 'paid_date';
            case 'year':
                return 'paid_date';
            default:
                return 'paid_date';
        }
    }
}
