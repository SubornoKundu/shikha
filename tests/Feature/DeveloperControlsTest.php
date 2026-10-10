<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureLicensedDomain;
use App\Models\Setting;
use App\Services\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/** The .env-only controls for the developer: branding lock, domain licence and feature switches. */
class DeveloperControlsTest extends TestCase
{
    use Concerns;
    use RefreshDatabase;

    private function settingsPayload(array $extra = []): array
    {
        return array_merge(['shipping_fee' => 60, 'free_shipping_over' => 0, 'low_stock_threshold' => 5], $extra);
    }

    public function test_branding_lock_ignores_name_logo_and_colour_from_the_admin_panel(): void
    {
        config(['shop.branding_lock' => true]);
        Setting::create(['key' => 'site_name', 'value' => 'Old saved name']);
        SiteSettings::applyToConfig();
        $this->assertNotSame('Old saved name', config('site.name'));

        $this->actingAs($this->makeAdmin())
            ->post('/admin/settings', $this->settingsPayload(['site_name' => 'Hacked', 'primary_color' => '#ff0000']))
            ->assertSessionHasNoErrors();

        $this->assertNotSame('Hacked', SiteSettings::all()['site_name']);
        $this->assertNotSame('#ff0000', SiteSettings::all()['primary_color']);
        $this->assertSame(60.0, (float) SiteSettings::all()['shipping_fee']); // everyday settings still save
    }

    public function test_branding_lock_off_lets_the_admin_edit_the_name(): void
    {
        config(['shop.branding_lock' => false]);

        $this->actingAs($this->makeAdmin())
            ->post('/admin/settings', $this->settingsPayload(['site_name' => 'My Shop']))
            ->assertSessionHasNoErrors();

        $this->assertSame('My Shop', SiteSettings::all()['site_name']);
    }

    public function test_settings_page_hides_branding_when_locked(): void
    {
        config(['shop.branding_lock' => true]);
        $this->actingAs($this->makeAdmin())->get('/admin/settings')
            ->assertInertia(fn ($p) => $p->where('brandingLocked', true));
    }

    public function test_licence_blocks_unlisted_domains_but_not_listed_or_local_ones(): void
    {
        config(['shop.licensed_domains' => ['rongshikha.com']]);
        $mw = new EnsureLicensedDomain;
        $run = fn (string $url) => $mw->handle(Request::create($url), fn () => response('ok'))->getStatusCode();

        $this->assertSame(403, $run('http://evil.com/'));
        $this->assertSame(403, $run('http://notrongshikha.com/'));
        $this->assertSame(200, $run('http://rongshikha.com/'));
        $this->assertSame(200, $run('http://www.rongshikha.com/'));
        $this->assertSame(200, $run('http://shop.rongshikha.com/'));
        $this->assertSame(200, $run('http://127.0.0.1/'));
        $this->assertSame(200, $run('http://client.test/'));

        config(['shop.licensed_domains' => []]); // empty list = no check
        $this->assertSame(200, $run('http://evil.com/'));
    }

    public function test_feature_switches_turn_pages_into_404(): void
    {
        config(['shop.features.wishlist' => false, 'shop.features.track_order' => false, 'shop.features.coupons' => false]);
        $user = $this->makeUser();

        $this->actingAs($user)->get('/wishlist')->assertNotFound();
        $this->get('/track-order')->assertNotFound();
        $this->actingAs($this->makeAdmin())->get('/admin/coupons')->assertNotFound();
    }

    public function test_features_are_on_by_default_and_shared_with_the_pages(): void
    {
        $this->get('/track-order')->assertOk();
        $this->get('/')->assertInertia(fn ($p) => $p->where('shop.features.wishlist', true)->where('shop.features.reviews', true));
    }
}
