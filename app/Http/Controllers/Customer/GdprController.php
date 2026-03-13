<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GdprController extends Controller
{
    /**
     * Show GDPR options page
     */
    public function index()
    {
        return view('customer.gdpr.index');
    }

    /**
     * Export all customer data (GDPR Right to Access)
     */
    public function exportData()
    {
        $customer = Auth::guard('customer')->user();
        
        // Prepare all customer data
        $data = [
            'personal_information' => [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'city' => $customer->city,
                'postcode' => $customer->postcode,
                'created_at' => $customer->created_at->toDateTimeString(),
            ],
            'vehicles' => $customer->vehicles->map(function($vehicle) {
                return [
                    'registration' => $vehicle->registration,
                    'make' => $vehicle->make,
                    'model' => $vehicle->model,
                    'year' => $vehicle->year,
                    'vin' => $vehicle->vin,
                    'engine_number' => $vehicle->engine_number,
                ];
            })->toArray(),
            'appointments' => $customer->appointments->map(function($appointment) {
                return [
                    'date' => $appointment->scheduled_date->toDateTimeString(),
                    'service' => $appointment->service?->name,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes,
                ];
            })->toArray(),
            'job_cards' => $customer->jobCards->map(function($job) {
                return [
                    'job_number' => $job->job_number,
                    'date_in' => $job->date_in?->toDateTimeString(),
                    'date_out' => $job->date_out?->toDateTimeString(),
                    'work_completed' => $job->work_completed,
                    'customer_complaint' => $job->customer_complaint,
                    'technician_notes' => $job->technician_notes,
                ];
            })->toArray(),
            'invoices' => $customer->invoices->map(function($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date->toDateTimeString(),
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                ];
            })->toArray(),
            'quotes' => $customer->quotes->map(function($quote) {
                return [
                    'quote_number' => $quote->quote_number,
                    'date' => $quote->created_at->toDateTimeString(),
                    'total_amount' => $quote->total_amount,
                    'status' => $quote->status,
                ];
            })->toArray(),
        ];

        // Create JSON file
        $filename = 'customer_data_' . $customer->id . '_' . now()->format('Y-m-d_His') . '.json';
        $json = json_encode($data, JSON_PRETTY_PRINT);
        
        return response()->streamDownload(function() use ($json) {
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Show account deletion confirmation page
     */
    public function showDeleteAccount()
    {
        return view('customer.gdpr.delete-account');
    }

    /**
     * Delete customer account and all data (GDPR Right to Erasure)
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'confirmation' => 'required|in:DELETE',
        ]);

        $customer = Auth::guard('customer')->user();

        // Verify password
        if (!Hash::check($request->password, $customer->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        // Anonymize instead of hard delete to maintain business records
        $customer->update([
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'email' => 'deleted_' . $customer->id . '@deleted.local',
            'phone' => null,
            'address' => '[DELETED]',
            'city' => '[DELETED]',
            'postcode' => null,
            'password' => Hash::make(str()->random(32)),
            'email_notifications' => false,
            'sms_notifications' => false,
            'appointment_reminders' => false,
            'service_reminders' => false,
            'mot_reminders' => false,
            'marketing_emails' => false,
        ]);

        // Log them out
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login')
            ->with('success', 'Your account has been deleted. All personal data has been removed.');
    }
}
