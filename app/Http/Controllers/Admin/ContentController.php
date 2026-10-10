<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InfoPages;
use App\Services\SiteContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Admin > Page content: the home page sections, the "Why us" points, the FAQ and the wording of the
 * information pages (About, Contact, Returns, Privacy, Terms).
 */
class ContentController extends Controller
{
    public function index(): Response
    {
        $custom = (array) SiteContent::get('pages');
        $defaults = InfoPages::defaults();

        return Inertia::render('Admin/Content/Index', [
            'sections' => SiteContent::sections(),
            'sectionLabels' => SiteContent::SECTIONS,
            'whyUs' => SiteContent::whyUs(),
            'icons' => SiteContent::ICONS,
            'faq' => array_values((array) SiteContent::get('faq')),
            'pages' => collect(InfoPages::PAGES)->map(fn (string $title, string $slug) => [
                'slug' => $slug,
                'title' => $title,
                'text' => trim((string) ($custom[$slug] ?? '')) !== '' ? (string) $custom[$slug] : InfoPages::toText($defaults[$slug]),
                'custom' => trim((string) ($custom[$slug] ?? '')) !== '',
            ])->values(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sections' => ['required', 'array', 'min:1', 'max:12'],
            'sections.*.key' => ['required', 'string', Rule::in(array_keys(SiteContent::SECTIONS))],
            'sections.*.visible' => ['required', 'boolean'],
            'sections.*.title' => ['nullable', 'string', 'max:80'],
            'whyUs' => ['present', 'array', 'max:8'],
            'whyUs.*.icon' => ['required', 'string', Rule::in(SiteContent::ICONS)],
            'whyUs.*.title' => ['required', 'string', 'max:100'],
            'whyUs.*.text' => ['required', 'string', 'max:400'],
            'whyUs.*.whatsapp' => ['required', 'boolean'],
            'faq' => ['present', 'array', 'max:20'],
            'faq.*.q' => ['required', 'string', 'max:200'],
            'faq.*.a' => ['required', 'string', 'max:1000'],
            'pages' => ['present', 'array'],
            'pages.*' => ['nullable', 'string', 'max:10000'],
        ], [
            'whyUs.*.title.required' => 'Every "Why us" point needs a title.',
            'whyUs.*.text.required' => 'Every "Why us" point needs some text.',
            'faq.*.q.required' => 'Every FAQ needs a question.',
            'faq.*.a.required' => 'Every FAQ needs an answer.',
        ]);

        // Keep the first copy of a section if the same one was sent twice.
        $seen = [];
        $sections = [];
        foreach ($data['sections'] as $s) {
            if (isset($seen[$s['key']])) {
                continue;
            }
            $seen[$s['key']] = true;
            $sections[] = ['key' => $s['key'], 'visible' => (bool) $s['visible'], 'title' => trim((string) ($s['title'] ?? ''))];
        }

        // A page whose text still equals the built-in wording is stored as "no custom text", so it keeps following the shop settings.
        $defaults = InfoPages::defaults();
        $pages = [];
        foreach (array_keys(InfoPages::PAGES) as $slug) {
            $text = trim(str_replace("\r", '', (string) ($data['pages'][$slug] ?? '')));
            $pages[$slug] = $text === '' || $text === InfoPages::toText($defaults[$slug]) ? '' : $text;
        }

        SiteContent::put('home.sections', $sections);
        SiteContent::put('why_us', collect($data['whyUs'])->map(fn ($i) => [
            'icon' => $i['icon'], 'title' => trim($i['title']), 'text' => trim($i['text']), 'whatsapp' => (bool) $i['whatsapp'],
        ])->values()->all());
        SiteContent::put('faq', collect($data['faq'])->map(fn ($i) => ['q' => trim($i['q']), 'a' => trim($i['a'])])->values()->all());
        SiteContent::put('pages', $pages);

        return back()->with('success', 'Page content saved.');
    }
    /** "Reset to default" for one block: the home sections, the Why us points, the FAQ, or one information page. */
    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'part' => ['required', 'string', Rule::in(array_merge(['sections', 'why_us', 'faq'], array_map(fn ($s) => 'page:'.$s, array_keys(InfoPages::PAGES))))],
        ]);

        $part = $data['part'];

        if ($part === 'sections') {
            SiteContent::reset('home.sections');
        } elseif ($part === 'why_us' || $part === 'faq') {
            SiteContent::reset($part);
        } else {
            $pages = (array) SiteContent::get('pages');
            $pages[substr($part, 5)] = '';
            SiteContent::put('pages', $pages);
        }

        return back()->with('success', 'Back to the standard wording.');
    }
}
