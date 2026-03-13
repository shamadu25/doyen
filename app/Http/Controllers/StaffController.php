<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StaffSchedule;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::with(['assignedJobs' => function ($q) {
            $q->whereIn('status', ['pending', 'in_progress']);
        }])
        ->whereNotNull('role')
        ->paginate(20);

        $stats = [
            'total' => User::whereNotNull('role')->count(),
            'active' => User::active()->whereNotNull('role')->count(),
            'technicians' => User::technicians()->count(),
            'working_today' => StaffSchedule::forDate(today())->active()->count(),
        ];

        return Inertia::render('Staff/Index', ['staff' => $staff, 'stats' => $stats]);
    }

    public function create()
    {
        return Inertia::render('Staff/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'nullable|string|unique:users,employee_id',
            'role' => 'required|in:admin,manager,technician,receptionist',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'hire_date' => 'required|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'skills' => 'nullable|array',
            'certifications' => 'nullable|array',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        // Generate employee ID if not provided
        if (empty($validated['employee_id'])) {
            $validated['employee_id'] = 'EMP-' . str_pad(User::max('id') + 1, 4, '0', STR_PAD_LEFT);
        }

        $user = User::create($validated);

        return redirect()->route('staff.show', $user)
            ->with('success', 'Staff member added successfully.');
    }

    public function show(User $staff)
    {
        $staff->load([
            'assignedJobs.vehicle.customer',
            'schedules' => function ($q) {
                $q->latest()->take(30);
            },
            'commissions' => function ($q) {
                $q->latest()->take(20);
            }
        ]);

        $stats = [
            'active_jobs' => $staff->assignedJobs()->whereIn('status', ['pending', 'in_progress'])->count(),
            'completed_jobs_month' => $staff->assignedJobs()->where('status', 'completed')
                ->whereMonth('updated_at', now()->month)->count(),
            'total_commission_month' => $staff->getMonthlyCommissions(),
            'hours_worked_month' => StaffSchedule::forUser($staff->id)
                ->whereMonth('date', now()->month)
                ->sum('hours_worked'),
        ];

        return Inertia::render('Staff/Show', ['staff' => $staff, 'stats' => $stats]);
    }

    public function edit(User $staff)
    {
        return Inertia::render('Staff/Edit', ['staff' => $staff]);
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'employee_id' => 'nullable|string|unique:users,employee_id,' . $staff->id,
            'role' => 'required|in:admin,manager,technician,receptionist',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'hire_date' => 'required|date',
            'hourly_rate' => 'nullable|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'skills' => 'nullable|array',
            'certifications' => 'nullable|array',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $staff->update($validated);

        return redirect()->route('staff.show', $staff)
            ->with('success', 'Staff member updated successfully.');
    }

    public function destroy(User $staff)
    {
        // Soft deactivate instead of delete
        $staff->update(['is_active' => false]);

        return redirect()->route('staff.index')
            ->with('success', 'Staff member deactivated successfully.');
    }

    public function schedule(Request $request, User $staff)
    {
        $date = $request->input('date', today());
        $schedules = $staff->schedules()
            ->whereBetween('date', [
                now()->parse($date)->startOfMonth(),
                now()->parse($date)->endOfMonth()
            ])
            ->get();

        return Inertia::render('Staff/Schedule', ['staff' => $staff, 'schedules' => $schedules, 'date' => $date]);
    }

    public function storeSchedule(Request $request, User $staff)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'notes' => 'nullable|string',
        ]);

        $validated['user_id'] = $staff->id;
        StaffSchedule::create($validated);

        return back()->with('success', 'Schedule created successfully.');
    }

    public function clockIn(User $staff)
    {
        $schedule = $staff->getTodaySchedule();
        
        if (!$schedule) {
            return back()->with('error', 'No schedule found for today.');
        }

        $schedule->clockIn();

        return back()->with('success', 'Clocked in successfully.');
    }

    public function clockOut(User $staff)
    {
        $schedule = $staff->getTodaySchedule();
        
        if (!$schedule || $schedule->status !== 'clocked_in') {
            return back()->with('error', 'Not currently clocked in.');
        }

        $schedule->clockOut();

        return back()->with('success', 'Clocked out successfully. Hours worked: ' . $schedule->hours_worked);
    }

    public function commissions(User $staff)
    {
        $commissions = $staff->commissions()
            ->with(['jobCard', 'invoice'])
            ->latest()
            ->paginate(20);

        $stats = [
            'pending' => $staff->commissions()->pending()->sum('commission_amount'),
            'approved' => $staff->commissions()->approved()->sum('commission_amount'),
            'paid_this_month' => $staff->commissions()->paid()
                ->whereMonth('paid_date', now()->month)->sum('commission_amount'),
            'total_earned' => $staff->commissions()->paid()->sum('commission_amount'),
        ];

        return Inertia::render('Staff/Commissions', ['staff' => $staff, 'commissions' => $commissions, 'stats' => $stats]);
    }

    public function approveCommission(Commission $commission)
    {
        $commission->approve();
        return back()->with('success', 'Commission approved.');
    }

    public function payCommission(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:commissions,id',
            'paid_date' => 'required|date',
        ]);

        Commission::whereIn('id', $validated['commission_ids'])
            ->where('status', 'approved')
            ->each(function ($commission) use ($validated) {
                $commission->markAsPaid($validated['paid_date']);
            });

        return back()->with('success', 'Commissions marked as paid.');
    }

    public function workload()
    {
        $technicians = User::technicians()
            ->with(['assignedJobs' => function ($q) {
                $q->whereIn('status', ['pending', 'in_progress']);
            }])
            ->get()
            ->map(function ($tech) {
                return [
                    'id' => $tech->id,
                    'name' => $tech->name,
                    'active_jobs' => $tech->assignedJobs->count(),
                    'skills' => $tech->skills ?? [],
                    'schedule' => $tech->getTodaySchedule(),
                ];
            });

        return Inertia::render('Staff/Workload', ['technicians' => $technicians]);
    }
}
