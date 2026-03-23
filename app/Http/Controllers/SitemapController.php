<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $baseUrl = rtrim(env('APP_URL', 'https://doyenautos.co.uk'), '/');
        // Use production URL if APP_URL is localhost
        if (str_contains($baseUrl, 'localhost') || str_contains($baseUrl, '127.0.0.1')) {
            $baseUrl = 'https://doyenautos.co.uk';
        }

        $urls = [
            [
                'loc'        => $baseUrl . '/',
                'lastmod'    => now()->toDateString(),
                'changefreq' => 'weekly',
                'priority'   => '1.0',
            ],
            [
                'loc'        => $baseUrl . '/about',
                'lastmod'    => now()->toDateString(),
                'changefreq' => 'monthly',
                'priority'   => '0.8',
            ],
            [
                'loc'        => $baseUrl . '/book-online',
                'lastmod'    => now()->toDateString(),
                'changefreq' => 'monthly',
                'priority'   => '0.9',
            ],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>{$url['loc']}</loc>\n";
            $xml .= "    <lastmod>{$url['lastmod']}</lastmod>\n";
            $xml .= "    <changefreq>{$url['changefreq']}</changefreq>\n";
            $xml .= "    <priority>{$url['priority']}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
