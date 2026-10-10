<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Browser-side protections on every response: no framing (clickjacking), no
 * MIME sniffing, a short referrer, no camera/microphone/payment prompts, and
 * HTTPS-only once the site is served over HTTPS.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), payment=(), usb=(), geolocation=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        // Content-Security-Policy: scripts, frames and form posts can only come from this site (plus the Google map frame),
        // so a script injected into a page cannot run or send data elsewhere. Off in local dev (the Vite dev server needs more).
        if (config('shop.csp') && ! app()->environment('local') && ! is_file(public_path('hot')) && ! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self'",
                "style-src 'self' 'unsafe-inline'",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data:",
                "connect-src 'self'",
                'frame-src https://www.google.com https://maps.google.com',
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ]));
        }

        // Admin, sign-in and account pages must never be kept in a shared or back-button cache.
        if ($request->is('admin', 'admin/*', 'login', 'register', 'forgot-password', 'reset-password', 'profile', 'profile/*', 'orders', 'orders/*', 'account/*', 'checkout', 'cart')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        return $response;
    }
}
