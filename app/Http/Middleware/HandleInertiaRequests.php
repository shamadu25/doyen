<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        // Compute base path for subdirectory deployments (e.g. XAMPP)
        $basePath = rtrim(parse_url(config('app.url'), PHP_URL_PATH) ?? '', '/');

        return array_merge(parent::share($request), [
            'appBasePath' => $basePath,
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role ?? 'admin',
                    'roles' => $request->user()->roles->pluck('name'),
                    'permissions' => $request->user()->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'garageSettings' => fn () => $this->getGarageSettings(),
        ]);
    }

    private function getGarageSettings(): array
    {
        try {
            $settings = Setting::pluck('value', 'key')->toArray();
            return [
                'garage_name' => $settings['garage_name'] ?? 'Doyen Auto Services',
                'address' => $settings['garage_address'] ?? $settings['address'] ?? '59 Southcroft Rd, Rutherglen, Glasgow, G73 1UG',
                'city' => $settings['garage_city'] ?? $settings['city'] ?? 'Rutherglen, Glasgow',
                'postcode' => $settings['garage_postcode'] ?? $settings['postcode'] ?? 'G73 1UG',
                'phone' => $settings['garage_phone'] ?? $settings['phone'] ?? '+44 7760 926245',
                'email' => $settings['garage_email'] ?? $settings['email'] ?? 'info@doyenautos.co.uk',
                'vat_number' => $settings['vat_number'] ?? '',
                'vat_rate' => (float) ($settings['vat_rate'] ?? 20),
                'logo_path' => $settings['logo_path'] ?? null,
                'default_labour_rate' => (float) ($settings['default_labour_rate'] ?? 65.00),
                'invoice_prefix' => $settings['invoice_prefix'] ?? 'INV',
                'invoice_terms' => $settings['invoice_terms'] ?? 'Payment due within 30 days.',
                'website' => $settings['website'] ?? '',
                'google_review_link' => env('GOOGLE_REVIEW_LINK', 'https://maps.app.goo.gl/dKnuaDHKtwtaHw3u5'),
                'whatsapp_number' => $settings['whatsapp_number'] ?? '',
            ];
        } catch (\Exception $e) {
            return [
                'garage_name' => 'Doyen Auto Services',
                'address' => '59 Southcroft Road',
                'city' => 'Rutherglen, Glasgow',
                'postcode' => 'G73 1UG',
                'phone' => '+44 7760 926245',
                'email' => 'info@doyenautos.co.uk',
                'vat_number' => '',
                'vat_rate' => 20,
                'logo_path' => null,
                'default_labour_rate' => 65.00,
                'invoice_prefix' => 'INV',
                'invoice_terms' => 'Payment due within 30 days.',
                'website' => '',
                'google_review_link' => env('GOOGLE_REVIEW_LINK', 'https://maps.app.goo.gl/dKnuaDHKtwtaHw3u5'),
                'whatsapp_number' => '',
            ];
        }
    }
}
