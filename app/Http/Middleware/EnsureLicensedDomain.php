<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SHOP_LICENSED_DOMAINS: this copy of the shop only runs on the listed domains. Anywhere else the visitor
 * gets a plain "not licensed" page. Local development (APP_ENV=local, localhost, 127.0.0.1, *.test, *.local)
 * are never blocked.
 */
class EnsureLicensedDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = (array) config('shop.licensed_domains', []);

        if ($allowed === [] || app()->environment('local') || $this->isLocalHost($request->getHost())) {
            return $next($request);
        }

        $host = strtolower($request->getHost());
        $bare = preg_replace('/^www\./', '', $host);

        foreach ($allowed as $domain) {
            $domain = preg_replace('/^www\./', '', strtolower($domain));
            if ($bare === $domain || str_ends_with($bare, '.'.$domain)) {
                return $next($request);
            }
        }

        return response(
            '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex"><title>Not available</title></head>'
            .'<body style="font-family:system-ui,sans-serif;display:flex;min-height:100vh;align-items:center;justify-content:center;margin:0;background:#f8fafc;color:#334155;text-align:center;padding:1.5rem">'
            .'<div><h1 style="font-size:1.4rem;margin:0 0 .5rem">This website is not licensed for this address</h1>'
            .'<p style="margin:0;color:#64748b">Please contact the developer.</p></div></body></html>',
            403,
        );
    }

    private function isLocalHost(string $host): bool
    {
        $host = strtolower($host);

        return in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)
            || str_ends_with($host, '.test') || str_ends_with($host, '.local') || str_ends_with($host, '.localhost');
    }
}
