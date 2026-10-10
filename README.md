# Rongshikha

Online shop for handmade paintings and textured wall art, Bangladesh. Laravel 13 (PHP 8.4), Vue 3, Inertia, Tailwind 4, MySQL.

## Setup

```bash
composer install
npm install
cp .env.example .env      # then fill DB_* and MAIL_*
php artisan key:generate
php artisan migrate --force
php artisan db:seed --class=RongshikhaSeeder         # paintings, sizes and prices, categories, banners, shop details
php artisan shop:admin homemadeelegance4050@gmail.com --password='YourStrongPassw0rd' --replace
npm run build
php artisan shop:check
```

Admin sign-in: `/admin/login`. Customers sign in at `/login`.

## How ordering works

- Customer pays 20% advance by bKash or Nagad (Personal, Send Money), the rest is cash on delivery. The customer enters the sending number and the Transaction ID.
- The remaining 80% is paid in cash to the delivery man.
- Cash on delivery (full amount) is also available.
- There is no online card gateway and no demo or test login.

## Accounts

- Admins are created with `php artisan shop:admin` or in Admin > Admins. At least one admin always remains.
- Forgot password is manual: the customer enters email, account phone and a new password. If they match the same customer, a request appears in Admin > Password requests. Call the number to confirm, then approve (or reject). Admin > Customers can also set a password directly.

## Emails

Only order processing updates are sent (`SHOP_ORDER_EMAILS=processing`).

## Developer controls (.env only)

These are for the developer. The admin panel cannot change them, so a client cannot re-brand or move the site.

| `.env` line | What it does |
|---|---|
| `SHOP_BRANDING_LOCK=true` | Shop name, tagline, search description, logo, favicon and brand colour come only from `.env` (`SHOP_NAME`, `SHOP_TAGLINE`, `SHOP_META_DESCRIPTION`, `SHOP_LOGO`, `SHOP_FAVICON`, `SHOP_BRAND_COLOR`). They disappear from Admin > Settings and anything saved there earlier is ignored. Set `false` on your own copy to edit them in the admin panel. |
| `SHOP_LICENSED_DOMAINS=shop.com` | The site only runs on these domains (comma separated). Elsewhere visitors see a "not licensed" page. Empty = no check. `localhost`, `127.0.0.1`, `*.test` and `APP_ENV=local` always work. |
| `FEATURE_REVIEWS`, `FEATURE_QA`, `FEATURE_WISHLIST`, `FEATURE_COUPONS`, `FEATURE_TRACK_ORDER` | `false` hides the feature from the shop and from the admin menu; its pages answer 404. |
| `SHOP_PAYMENT_METHODS=bkash,nagad` | Which payment methods customers can pick. |
| `SHOP_SETTINGS_ACCESS` | `false` hides Admin > Settings completely. |
| `SHOP_CREDIT_TEXT`, `SHOP_CREDIT_URL` | The small credit line in the footer. Empty text hides it. |

After changing `.env` on the server run `php artisan config:clear` (and `php artisan config:cache` if you use it).

## Tests

```bash
php artisan test
```

## Security notes

Rate limiting on sign-in, reset and checkout; security headers; uploads are re-encoded and cannot run scripts; `.env` is blocked by `public/.htaccess`. Keep `APP_DEBUG=false` in production and use a strong admin password.
