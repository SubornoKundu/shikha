<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Services\InfoPages;
use App\Services\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use Concerns;
    use RefreshDatabase;

    private function payload(array $override = []): array
    {
        return array_merge([
            'sections' => SiteContent::sections(),
            'whyUs' => SiteContent::whyUs(),
            'faq' => [],
            'pages' => [],
        ], $override);
    }

    public function test_customers_cannot_open_the_content_page(): void
    {
        $this->actingAs($this->makeUser())->get('/admin/content')->assertForbidden();
    }

    public function test_content_page_renders_for_admin(): void
    {
        $this->actingAs($this->makeAdmin())->get('/admin/content')
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Admin/Content/Index')
                ->has('sections')->has('whyUs')->has('pages', count(InfoPages::PAGES)));
    }

    public function test_admin_edits_reach_the_storefront(): void
    {
        $this->actingAs($this->makeAdmin());

        $sections = collect(SiteContent::sections())
            ->map(fn ($s) => $s['key'] === 'faq' ? $s + [] : $s)
            ->reject(fn ($s) => $s['key'] === 'faq')
            ->prepend(collect(SiteContent::sections())->firstWhere('key', 'faq'))
            ->map(fn ($s) => $s['key'] === 'map' ? array_merge($s, ['visible' => false]) : $s)
            ->values()->all();

        $why = SiteContent::whyUs();
        $why[0]['title'] = 'Edited why title';

        $this->post('/admin/content', $this->payload([
            'sections' => $sections,
            'whyUs' => $why,
            'faq' => [['q' => 'Custom sizes?', 'a' => 'Yes, advance is {advance}%.']],
            'pages' => ['about' => "Intro text.\n\n## Our story\n\nHand painted."],
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Edited why title', SiteContent::whyUs()[0]['title']);
        $this->assertSame('faq', SiteContent::sections()[0]['key']);
        $this->assertSame('Custom sizes?', SiteContent::faq()[0]['q']);
        $this->assertStringNotContainsString('{advance}', SiteContent::faq()[0]['a']);

        $this->get('/')->assertInertia(fn (Assert $p) => $p->where('homeSections.0.key', 'faq'));
        $this->get('/about')->assertOk()->assertSee('Our story', false);
    }

    public function test_validation_rejects_bad_input(): void
    {
        $this->actingAs($this->makeAdmin());
        $this->post('/admin/content', $this->payload(['faq' => [['q' => '', 'a' => 'x']]]))->assertSessionHasErrors('faq.0.q');
        $this->post('/admin/content', $this->payload(['whyUs' => [['icon' => 'nope', 'title' => 't', 'text' => 't', 'whatsapp' => false]]]))->assertSessionHasErrors('whyUs.0.icon');
    }

    public function test_category_display_order_is_respected_and_new_ones_go_last(): void
    {
        $this->actingAs($this->makeAdmin());
        $a = Category::create(['name' => 'Alpha', 'slug' => 'alpha', 'sort_order' => 20]);
        $b = Category::create(['name' => 'Beta', 'slug' => 'beta', 'sort_order' => 10]);

        $this->post('/admin/categories', ['name' => 'Gamma'])->assertSessionHasNoErrors();

        $ordered = Category::inShopOrder(Category::whereNull('parent_id')->get())->pluck('slug')->all();
        $this->assertSame(['beta', 'alpha', 'gamma'], $ordered);
        $this->assertGreaterThan(20, Category::where('slug', 'gamma')->value('sort_order'));
    }

    public function test_reset_puts_a_block_back_to_the_standard_wording(): void
    {
        $this->actingAs($this->makeAdmin());
        $standardTitle = SiteContent::whyUs()[0]['title'];

        $why = SiteContent::whyUs();
        $why[0]['title'] = 'Changed';
        $this->post('/admin/content', $this->payload(['whyUs' => $why, 'pages' => ['about' => 'Own words.']]));
        $this->assertSame('Changed', SiteContent::whyUs()[0]['title']);

        $this->post('/admin/content/reset', ['part' => 'why_us'])->assertSessionHasNoErrors();
        $this->assertSame($standardTitle, SiteContent::whyUs()[0]['title']);

        $this->post('/admin/content/reset', ['part' => 'page:about'])->assertSessionHasNoErrors();
        $this->get('/about')->assertDontSee('Own words.');

        $this->post('/admin/content/reset', ['part' => 'nonsense'])->assertSessionHasErrors('part');
    }

    public function test_help_page_and_setup_checklist(): void
    {
        $this->actingAs($this->makeUser())->get('/admin/help')->assertForbidden();

        $this->actingAs($this->makeAdmin())->get('/admin/help')
            ->assertOk()->assertInertia(fn (Assert $p) => $p->component('Admin/Help/Index'));

        $this->get('/admin')->assertOk()->assertInertia(fn (Assert $p) => $p->component('Admin/Dashboard')->has('setup'));
    }
}
