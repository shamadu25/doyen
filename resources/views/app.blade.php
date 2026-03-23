<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    {{-- Default title & description (overridden per-page via <Head> in Vue) --}}
    <title inertia>{{ config('app.name', 'Doyen Auto Services') }}</title>
    <meta name="description" content="Expert vehicle diagnostics, ECU repair, airbag SRS reset, MOT testing and servicing in Rutherglen, Glasgow. Dealer-level equipment. 16+ years experience." />

    {{-- Theme / brand colour --}}
    <meta name="theme-color" content="#1d4ed8" />
    <meta name="msapplication-TileColor" content="#1d4ed8" />

    {{-- Favicon chain --}}
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <link rel="manifest" href="/site.webmanifest" />

    {{-- Canonical (default; public pages override via <Head>) --}}
    <link rel="canonical" href="{{ url()->current() }}" />

    {{-- Default Open Graph (public pages override via <Head>) --}}
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Doyen Auto Services" />
    <meta property="og:locale" content="en_GB" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Doyen Auto Services | Glasgow Auto Diagnostic Specialists" />
    <meta property="og:description" content="Expert vehicle diagnostics, ECU repair, airbag SRS reset, MOT testing and servicing in Rutherglen, Glasgow. Dealer-level equipment. 16+ years experience." />
    <meta property="og:image" content="{{ asset('images/og-image.jpg') }}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:image:alt" content="Doyen Auto Services — Glasgow Vehicle Diagnostics" />

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Doyen Auto Services | Glasgow Auto Diagnostic Specialists" />
    <meta name="twitter:description" content="Expert vehicle diagnostics, ECU repair, airbag SRS reset, MOT testing and servicing in Rutherglen, Glasgow." />
    <meta name="twitter:image" content="{{ asset('images/og-image.jpg') }}" />

    {{-- Google / Search --}}
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="googlebot" content="index, follow" />

    {{-- Geo tags --}}
    <meta name="geo.region" content="GB-SCT" />
    <meta name="geo.placename" content="Rutherglen, Glasgow" />
    <meta name="geo.position" content="55.8272;-4.2218" />
    <meta name="ICBM" content="55.8272, -4.2218" />

    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="font-sans antialiased h-full bg-gray-50">
    @inertia
</body>
</html>
