<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $customer = Auth::guard('customer')->user();
        return view('customer.profile.edit', compact('customer'));
    }

    public function update(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string',
            'mobile' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'postcode' => 'required|string',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($validated['current_password'], $customer->password)) {
            return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
        }

        $customer->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateNotifications(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'appointment_reminders' => 'boolean',
            'service_reminders' => 'boolean',
            'mot_reminders' => 'boolean',
            'marketing_emails' => 'boolean',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Notification preferences updated.');
    }
}
