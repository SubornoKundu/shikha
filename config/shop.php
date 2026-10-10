<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storefront Settings
    |--------------------------------------------------------------------------
    */

    // Flat shipping fee added to every order at checkout.
    'shipping_fee' => (float) env('SHOP_SHIPPING_FEE', 60),

    // Orders whose total (after discount) reaches this amount ship free.
    // Set to 0 to disable free shipping.
    'free_shipping_over' => (float) env('SHOP_FREE_SHIPPING_OVER', 0),

    // Products with this many units (or fewer) left show up as "low stock"
    // on the admin dashboard.
    'low_stock_threshold' => (int) env('SHOP_LOW_STOCK_THRESHOLD', 5),

    'currency' => env('SHOP_CURRENCY', 'BDT'),

    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', '৳'),


    /*
    |--------------------------------------------------------------------------
    | Contact details, WhatsApp and social links (header / footer)
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'phone' => env('SHOP_PHONE', '+880 1907-852281'),
        'email' => env('SHOP_EMAIL', 'homemadeelegance4050@gmail.com'),
        'address' => env('SHOP_ADDRESS', ''),
        'hours' => env('SHOP_HOURS', ''),
    ],

    // International format without "+" or spaces, used for wa.me links.
    'whatsapp' => env('SHOP_WHATSAPP', '8801907852281'),

    'social' => [
        'facebook' => env('SHOP_FACEBOOK'),
        'instagram' => env('SHOP_INSTAGRAM'),
        'youtube' => env('SHOP_YOUTUBE'),
        'tiktok' => env('SHOP_TIKTOK'),
        'x' => env('SHOP_X'),
    ],

    // Shown in the "Shipping Terms" tab when a product has no text of its own.
    'default_shipping_info' => env(
        'SHOP_SHIPPING_INFO',
        "Every painting is packed carefully before it is sent.\nDelivery charge is added at checkout. We confirm the delivery time with you when we confirm your order.\nPay 20% in advance by bKash or Nagad (Send Money), then enter the transaction ID at checkout. The rest is paid in cash when the painting arrives.\nIf a painting arrives damaged, tell us within 7 days of delivery and we will make it right."
    ),

    // Manual mobile-banking numbers shown at checkout.
    'payments' => [
        'bkash' => ['number' => env('SHOP_BKASH_NUMBER', '01907852281'), 'type' => env('SHOP_BKASH_TYPE', 'Personal')],
        'nagad' => ['number' => env('SHOP_NAGAD_NUMBER', ''), 'type' => env('SHOP_NAGAD_TYPE', 'Personal')],
    ],

    // Which payment methods checkout accepts: a comma list of cod, bkash, nagad.
    // Rongshikha takes a bKash or Nagad advance, the rest is cash on delivery (SHOP_PAYMENT_METHODS=bkash,nagad). The automated tests
    // exercise every method, so they get the full list unless a test sets this itself.
    'payment_methods' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('SHOP_PAYMENT_METHODS', app()->runningUnitTests() ? 'cod,bkash,nagad' : 'bkash,nagad'))
    ))),

    // Off: the brand colour setting is ignored, so nobody can break the deep green and gold design from the admin.
    'brand_colour_editable' => (bool) env('SHOP_BRAND_COLOUR', env('APP_ENV') === 'testing'),

    // Which order events e-mail the customer: placed, paid, processing, shipped, completed, cancelled.
    // Comma separated in .env (SHOP_ORDER_EMAILS=processing sends only when the order is processed).
    'order_emails' => array_values(array_filter(array_map('trim', explode(',', (string) env(
        'SHOP_ORDER_EMAILS',
        'placed,paid,processing,shipped,completed,cancelled',
    ))))),

    // Part-payment for bKash / Nagad orders: this % of the total is paid up front,
    // the rest in cash to the delivery man. 0 = the customer pays everything now.
    'advance_percent' => (int) env('SHOP_ADVANCE_PERCENT', 20),

    // Paintings at or above this price (after discount) count as "statement pieces"; below it, "small canvases".
    // Drives the Statement pieces section on the home page and the quick price filters.
    'premium_from' => (int) env('SHOP_PREMIUM_FROM', 2000),

    // What the "Visit us" map searches for (a Google Plus Code plus the area works well).
    // Leave both empty for an online-only shop: the map block is then hidden.
    'map_link' => env('SHOP_MAP_LINK', ''),
    'map_query' => env('SHOP_MAP_QUERY', ''),

    // Scaling: how long (seconds) the home page blocks, the best-seller ranking and the
    // admin dashboard totals may be reused before they are recalculated. Admin edits to
    // products / brands / categories / banners refresh the storefront immediately.
    // 0 turns caching off (that is what the automated tests use).
    'storefront_cache_seconds' => (int) env('SHOP_STOREFRONT_CACHE', app()->runningUnitTests() ? 0 : 120),
    'popular_cache_seconds' => (int) env('SHOP_POPULAR_CACHE', app()->runningUnitTests() ? 0 : 600),
    'stats_cache_seconds' => (int) env('SHOP_STATS_CACHE', app()->runningUnitTests() ? 0 : 120),

    // Admin > Settings (shop name, logo, colours...) is hidden from the admin panel and its
    // address gives "not found" unless this is true. Keep it false on a site you hand over.
    // Admin > Settings (shop name, contact, payment numbers, map...). On by default so the owner controls everything from the admin panel.
    'settings_access' => (bool) env('SHOP_SETTINGS_ACCESS', true),

    /*
    |--------------------------------------------------------------------------
    | Developer / reseller controls (set in .env only; the admin panel cannot change these)
    |--------------------------------------------------------------------------
    */

    // true  = site name, tagline, search description, logo, favicon and brand colour come ONLY from .env
    //         (SHOP_NAME, SHOP_TAGLINE, SHOP_META_DESCRIPTION, SHOP_LOGO, SHOP_FAVICON, SHOP_BRAND_COLOR).
    //         They disappear from Admin > Settings, and anything saved there earlier is ignored.
    // false = the owner can edit them in Admin > Settings (use this on your own copy).
    'branding_lock' => (bool) env('SHOP_BRANDING_LOCK', true),

    // Comma-separated domains this copy may run on, e.g. "rongshikha.com". On any other domain the
    // visitor only sees a "not licensed" page. Empty = no check. localhost / 127.0.0.1 / *.test always work.
    'licensed_domains' => array_values(array_filter(array_map(
        fn ($d) => strtolower(trim((string) $d)),
        explode(',', (string) env('SHOP_LICENSED_DOMAINS', '')),
    ))),

    // Feature switches: false removes the feature from the shop AND from the admin menu (its pages answer 404).
    'features' => [
        'reviews' => (bool) env('FEATURE_REVIEWS', true),
        'qa' => (bool) env('FEATURE_QA', true),
        'wishlist' => (bool) env('FEATURE_WISHLIST', true),
        'coupons' => (bool) env('FEATURE_COUPONS', true),
        'track_order' => (bool) env('FEATURE_TRACK_ORDER', true),
    ],

    // Content-Security-Policy header (see SecurityHeaders). Leave on; set SHOP_CSP=false only if a third-party widget you add must run.
    'csp' => (bool) env('SHOP_CSP', true),

    // Small credit line in the footer. Leave SHOP_CREDIT_TEXT empty to hide it.
    'credit' => [
        'text' => (string) env('SHOP_CREDIT_TEXT', 'Developed by Xovelt Labs'),
        'url' => (string) env('SHOP_CREDIT_URL', ''),
    ],

    // Behind Cloudflare / a load balancer / a hosting proxy: set TRUSTED_PROXIES=* (or a comma list
    // of proxy IPs) in .env so visitors' real IP addresses are used (login limits are per IP).
    'trusted_proxies' => env('TRUSTED_PROXIES'),
];
