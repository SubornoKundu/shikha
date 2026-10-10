<?php

namespace App\Http\Controllers;

use App\Services\InfoPages;
use Inertia\Inertia;
use Inertia\Response;

/**
 * About / Contact / Returns / Privacy / Terms. The wording is generic but uses
 * the shop's own settings (name, contact details, shipping fee, free-shipping
 * limit), so it follows whatever the admin saves under Settings.
 */
class PageController extends Controller
{
    /** slug => page title */
    public const PAGES = InfoPages::PAGES;

    public function show(string $slug): Response
    {
        abort_unless(isset(self::PAGES[$slug]), 404);

        $page = InfoPages::resolve($slug);

        $shop = (string) config('site.name', config('app.name', 'Rongshikha'));
        $this->seo(self::PAGES[$slug].' - '.$shop, $page['intro']);

        return Inertia::render('Info/Show', [
            'slug' => $slug,
            'title' => self::PAGES[$slug],
            'intro' => $page['intro'],
            'sections' => $page['sections'],
        ]);
    }
}
