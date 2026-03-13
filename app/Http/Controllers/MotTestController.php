<?php

namespace App\Http\Controllers;

use App\Http\Requests\MotTestRequest;
use App\Models\ActivityLog;
use App\Models\MotTest;
use App\Models\Reminder;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MotTestController extends Controller
{
    public function index(Request $request)
    {
        $motTests = MotTest::query()
            ->with(['vehicle.customer'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('vehicle', fn($q) => $q->where('registration_number', 'like', "%{$search}%"))
                      ->orWhere('test_number', 'like', "%{$search}%");
            })
            ->when($request->result, fn($q, $r) => $q->where('test_result', $r))
            ->latest('test_date')
            ->paginate(15)
            ->withQueryString();

        // MOT Performance stats
        $totalTests = MotTest::count();
        $passed = MotTest::where('test_result', 'passed')->count();
        $failed = MotTest::where('test_result', 'failed')->count();
        $passRate = $totalTests > 0 ? round(($passed / $totalTests) * 100, 1) : 0;

        return Inertia::render('MotTests/Index', [
            'motTests' => $motTests,
            'filters' => $request->only('search', 'result'),
            'stats' => compact('totalTests', 'passed', 'failed', 'passRate'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('MotTests/Create', [
            'vehicles' => Vehicle::with('customer')->select('id', 'customer_id', 'registration_number', 'make', 'model', 'mot_due_date')->get(),
            'preselectedVehicleId' => $request->vehicle_id,
        ]);
    }

    public function store(MotTestRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('certificate')) {
            $data['certificate_path'] = $request->file('certificate')->store('mot-certificates', 'public');
        }

        $motTest = MotTest::create($data);

        // Update vehicle MOT expiry if passed
        if ($motTest->test_result === 'passed' && $motTest->expiry_date) {
            $motTest->vehicle->update(['mot_due_date' => $motTest->expiry_date]);
        }

        ActivityLog::log('created', "MOT test recorded for {$motTest->vehicle->registration_number}", $motTest);
        return redirect()->route('mot-tests.show', $motTest)->with('success', 'MOT test recorded.');
    }

    public function show(MotTest $motTest)
    {
        $motTest->load(['vehicle.customer', 'jobCard']);
        return Inertia::render('MotTests/Show', ['motTest' => $motTest]);
    }

    public function edit(MotTest $motTest)
    {
        return Inertia::render('MotTests/Edit', [
            'motTest' => $motTest,
            'vehicles' => Vehicle::with('customer')->get(['id', 'customer_id', 'registration_number', 'make', 'model']),
        ]);
    }

    public function update(MotTestRequest $request, MotTest $motTest)
    {
        $data = $request->validated();

        if ($request->hasFile('certificate')) {
            $data['certificate_path'] = $request->file('certificate')->store('mot-certificates', 'public');
        }

        $motTest->update($data);

        if ($motTest->test_result === 'passed' && $motTest->expiry_date) {
            $motTest->vehicle->update(['mot_due_date' => $motTest->expiry_date]);
        }

        return redirect()->route('mot-tests.show', $motTest)->with('success', 'MOT test updated.');
    }

    public function pass(MotTest $motTest)
    {
        $motTest->update([
            'test_result' => 'passed',
            'test_date' => $motTest->test_date ?? now(),
            'expiry_date' => $motTest->expiry_date ?? now()->addYear(),
        ]);

        $motTest->vehicle->update(['mot_due_date' => $motTest->expiry_date]);
        ActivityLog::log('mot_passed', "MOT passed for {$motTest->vehicle->registration_number}", $motTest);

        return back()->with('success', 'MOT marked as passed.');
    }

    public function fail(Request $request, MotTest $motTest)
    {
        $motTest->update([
            'test_result' => 'failed',
            'test_date' => $motTest->test_date ?? now(),
            'failures' => $request->failures ?? [],
        ]);

        ActivityLog::log('mot_failed', "MOT failed for {$motTest->vehicle->registration_number}", $motTest);
        return back()->with('success', 'MOT marked as failed.');
    }

    public function retest(MotTest $motTest)
    {
        $retest = MotTest::create([
            'vehicle_id' => $motTest->vehicle_id,
            'job_card_id' => $motTest->job_card_id,
            'test_result' => 'retest',
            'mileage' => $motTest->mileage,
            'tester_name' => $motTest->tester_name,
            'notes' => 'Retest of MOT #' . $motTest->id,
        ]);

        return redirect()->route('mot-tests.show', $retest)->with('success', 'Retest created.');
    }

    public function destroy(MotTest $motTest)
    {
        $motTest->delete();
        return redirect()->route('mot-tests.index')->with('success', 'MOT test record deleted.');
    }
}
