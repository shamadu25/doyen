<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllSettings();

        return Inertia::render('Settings/Index', [
            'settings' => [
                'garage_name' => $settings['garage_name'] ?? 'Doyen Auto Services',
                'address' => $settings['address'] ?? '',
                'city' => $settings['city'] ?? '',
                'postcode' => $settings['postcode'] ?? '',
                'phone' => $settings['phone'] ?? '',
                'email' => $settings['email'] ?? '',
                'website' => $settings['website'] ?? '',
                'vat_number' => $settings['vat_number'] ?? '',
                'vat_rate' => $settings['vat_rate'] ?? '20',
                'default_labour_rate' => $settings['default_labour_rate'] ?? '65.00',
                'invoice_prefix' => $settings['invoice_prefix'] ?? 'INV',
                'invoice_terms' => $settings['invoice_terms'] ?? 'Payment due within 30 days.',
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'garage_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'vat_number' => 'nullable|string|max:20',
            'vat_rate' => 'required|numeric|min:0|max:100',
            'default_labour_rate' => 'required|numeric|min:0',
            'invoice_prefix' => 'nullable|string|max:10',
            'invoice_terms' => 'nullable|string|max:2000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'garage');
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            Setting::set('logo_path', $path, 'garage');
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
