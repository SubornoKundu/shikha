<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Route guard for the .env feature switches (config shop.features): `feature:wishlist`, `feature:coupons`... */
class EnsureFeatureOn
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless((bool) config("shop.features.{$feature}", true), 404);

        return $next($request);
    }
}
