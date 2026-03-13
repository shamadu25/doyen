<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'stripe' => [
        'key' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'dvla' => [
        'api_key' => env('DVLA_API_KEY'),
        'base_url' => 'https://driver-vehicle-licensing.api.gov.uk/vehicle-enquiry/v1',
    ],

    'vehicledataglobal' => [
        'api_key'  => env('VDG_API_KEY'),
        'base_url' => env('VDG_API_URL', 'https://uk1.ukvehicledata.co.uk/api/datapackage/VehicleAndMotHistory'),
    ],

    'dvsa' => [
        'client_id' => env('DVSA_CLIENT_ID'),
        'client_secret' => env('DVSA_CLIENT_SECRET'),
        'api_key' => env('DVSA_API_KEY'),
        'scope' => env('DVSA_SCOPE_URL', 'https://tapi.dvsa.gov.uk/.default'),
        'token_url' => env('DVSA_TOKEN_URL', 'https://login.microsoftonline.com/a455b827-244f-4c97-b5b4-ce5d13b4d00c/oauth2/v2.0/token'),
        'base_url' => env('DVSA_API_BASE_URL', 'https://tapi.dvsa.gov.uk/v1/trade/vehicles/mot-tests'),
    ],

    'tecdoc' => [
        'api_key' => env('TECDOC_API_KEY'),
        'provider_id' => env('TECDOC_PROVIDER_ID'),
    ],

    // Parts Supplier APIs
    'parts_supplier' => [
        'default' => env('PARTS_SUPPLIER_DEFAULT', 'eurocarparts'),
    ],

    'eurocarparts' => [
        'api_key' => env('EUROCARPARTS_API_KEY'),
        'base_url' => env('EUROCARPARTS_API_URL', 'https://api.eurocarparts.com/v1'),
    ],

    'gsf' => [
        'api_key' => env('GSF_API_KEY'),
        'base_url' => env('GSF_API_URL', 'https://api.gsfcarparts.com/v1'),
    ],

    'autodoc' => [
        'api_key' => env('AUTODOC_API_KEY'),
        'base_url' => env('AUTODOC_API_URL', 'https://webservice.autodoc.de/api'),
    ],

    'oscaro' => [
        'api_key' => env('OSCARO_API_KEY'),
        'base_url' => env('OSCARO_API_URL', 'https://api.oscaro.com/v1'),
    ],

    // SMS Service (Twilio)
    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'whatsapp_enabled' => env('WHATSAPP_ENABLED', false),
        'whatsapp_from' => env('WHATSAPP_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tawk.to Live Chat
    |--------------------------------------------------------------------------
    |
    | Free live chat widget for customer support
    | Sign up at: https://www.tawk.to
    | Get your property_id and widget_id from the widget setup page
    |
    */

    'tawk' => [
        'enabled' => env('TAWK_ENABLED', false),
        'property_id' => env('TAWK_PROPERTY_ID'),
        'widget_id' => env('TAWK_WIDGET_ID', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Business Integration
    |--------------------------------------------------------------------------
    |
    | Google My Business review link and place ID
    | Get your place ID: https://developers.google.com/maps/documentation/places/web-service/place-id
    |
    */

    'google' => [
        'review_link' => env('GOOGLE_REVIEW_LINK', 'https://g.page/r/YOUR_PLACE_ID/review'),
        'place_id' => env('GOOGLE_PLACE_ID'),
    ],

];
