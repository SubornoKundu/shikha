<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Text the admin edits in Admin > Page content: the home page sections (order, show/hide, headings),
 * the "Why us" points, the FAQ and the wording of the About / Returns / Terms / Privacy / Contact pages.
 *
 * Values live in the `site_contents` table. Anything the admin has not changed uses the defaults below,
 * so a fresh install looks right without any setup.
 */
class SiteContent
{
    private const CACHE_KEY = 'site.contents';

    /** Home page sections the admin can reorder / hide. The banner always stays on top. */
    public const SECTIONS = [
        'categories' => 'Categories (picture row)',
        'featured' => 'Featured paintings',
        'collections' => 'Category-wise paintings',
        'faq' => 'FAQ',
        'why_us' => 'Why us',
        'map' => 'Location map',
    ];

    public const ICONS = ['package-open', 'frame', 'hand-heart', 'truck', 'shield-check', 'gift', 'star', 'paintbrush', 'sparkles', 'phone'];

    /** @return array<string, mixed> */
    public static function defaults(): array
    {
        return [
            'home.sections' => [
                ['key' => 'categories', 'visible' => true, 'title' => 'Shop by category'],
                ['key' => 'featured', 'visible' => true, 'title' => 'Featured paintings'],
                ['key' => 'collections', 'visible' => true, 'title' => ''],
                ['key' => 'faq', 'visible' => true, 'title' => 'Questions & answers'],
                ['key' => 'why_us', 'visible' => true, 'title' => 'Why Rongshikha'],
                ['key' => 'map', 'visible' => true, 'title' => ''],
            ],
            'why_us' => [
                ['icon' => 'package-open', 'title' => 'Open it in front of the rider', 'text' => 'Unwrap the parcel in front of the delivery rider and check your painting. If anything is wrong, tell us right away and we will make it right.', 'whatsapp' => false],
                ['icon' => 'frame', 'title' => 'Not sure it will suit your wall?', 'text' => 'Send a photo of your wall on WhatsApp and we will tell you which size and colours will look best in your room.', 'whatsapp' => true],
                ['icon' => 'hand-heart', 'title' => 'Every piece is handmade by the artist', 'text' => 'Not a machine-printed poster. Every painting is painted and textured by hand, one at a time.', 'whatsapp' => false],
                ['icon' => 'truck', 'title' => 'Safe delivery across Bangladesh', 'text' => 'Carefully packed and delivered all over Bangladesh. Getting your painting to you in perfect condition, even outside Dhaka, is our promise.', 'whatsapp' => false],
            ],
            // {advance} = advance percent, {rest} = the rest paid on delivery, {fee} = delivery charge (from Settings).
            'faq' => [
                ['q' => 'How do I order a painting?', 'a' => 'Choose a painting and a size, add it to your cart and check out with your delivery address. Pay the advance by bKash or Nagad, enter the transaction ID, and we get your painting ready to send.'],
                ['q' => 'How does payment work?', 'a' => 'You send {advance}% of the total by bKash or Nagad (Send Money) when you order. The other {rest}% you pay in cash to the delivery man.'],
                ['q' => 'Do you deliver all over Bangladesh?', 'a' => 'Yes, we deliver safely across all of Bangladesh. Delivery charge is {fee}. Every painting is wrapped carefully so it reaches you undamaged.'],
                ['q' => 'Are the paintings really handmade?', 'a' => 'Yes. Every painting is made by hand, one at a time. They are not machine-printed posters, so small differences from the photo are natural.'],
                ['q' => 'What if my painting arrives damaged?', 'a' => 'Open the package in front of the rider and tell us straight away if something is wrong. You can also message us within 7 days of delivery and we will sort it out.'],
                ['q' => 'Which size will suit my wall?', 'a' => 'Send us a photo of your wall on WhatsApp and we will suggest the size and colours that suit it best.'],
            ],
            'pages' => [],
        ];
    }

    /** @return array<string, mixed> */
    private static function stored(): array
    {
        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $rows = DB::table('site_contents')->pluck('value', 'key')->map(fn ($v) => json_decode((string) $v, true))->all();
        } catch (\Throwable) {
            return []; // table not migrated yet
        }

        Cache::forever(self::CACHE_KEY, $rows);

        return $rows;
    }

    public static function get(string $key): mixed
    {
        $stored = self::stored();

        if (array_key_exists($key, $stored) && $stored[$key] !== null) {
            return $stored[$key];
        }

        return self::defaults()[$key] ?? null;
    }

    public static function put(string $key, mixed $value): void
    {
        DB::table('site_contents')->updateOrInsert(
            ['key' => $key],
            ['value' => json_encode($value, JSON_UNESCAPED_UNICODE), 'updated_at' => now(), 'created_at' => now()],
        );

        Cache::forget(self::CACHE_KEY);
        StorefrontCache::flush();
    }

    /** Back to the built-in text for one key. */
    public static function reset(string $key): void
    {
        DB::table('site_contents')->where('key', $key)->delete();

        Cache::forget(self::CACHE_KEY);
        StorefrontCache::flush();
    }

    /**
     * Home page sections in the admin's order. A section the admin has not listed (added in a later
     * version) is appended, so nothing ever disappears by accident.
     *
     * @return list<array{key: string, visible: bool, title: string}>
     */
    public static function sections(): array
    {
        $saved = collect((array) self::get('home.sections'))->filter(fn ($s) => isset($s['key'], self::SECTIONS[$s['key']]));
        $keys = $saved->pluck('key')->all();
        $out = $saved->map(fn ($s) => ['key' => (string) $s['key'], 'visible' => (bool) ($s['visible'] ?? true), 'title' => (string) ($s['title'] ?? '')])->all();

        foreach (self::defaults()['home.sections'] as $default) {
            if (! in_array($default['key'], $keys, true)) {
                $out[] = $default;
            }
        }

        return array_values($out);
    }

    /** @return list<array{icon: string, title: string, text: string, whatsapp: bool}> */
    public static function whyUs(): array
    {
        return collect((array) self::get('why_us'))
            ->map(fn ($i) => [
                'icon' => in_array($i['icon'] ?? '', self::ICONS, true) ? $i['icon'] : 'star',
                'title' => (string) ($i['title'] ?? ''),
                'text' => (string) ($i['text'] ?? ''),
                'whatsapp' => (bool) ($i['whatsapp'] ?? false),
            ])
            ->values()
            ->all();
    }

    /** FAQ with {advance}, {rest} and {fee} filled in from the shop settings. @return list<array{q: string, a: string}> */
    public static function faq(): array
    {
        $advance = (int) config('shop.advance_percent');
        $sym = (string) config('shop.currency_symbol', '৳');
        $replace = [
            '{advance}' => $advance > 0 ? (string) $advance : '20',
            '{rest}' => (string) (100 - ($advance > 0 ? $advance : 20)),
            '{fee}' => $sym.number_format((float) config('shop.shipping_fee')),
        ];

        return collect((array) self::get('faq'))
            ->map(fn ($i) => ['q' => (string) ($i['q'] ?? ''), 'a' => strtr((string) ($i['a'] ?? ''), $replace)])
            ->filter(fn ($i) => $i['q'] !== '' && $i['a'] !== '')
            ->values()
            ->all();
    }
}
