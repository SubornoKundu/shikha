<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use Concerns;
    use RefreshDatabase;

    public function test_pages_carry_a_content_security_policy(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    public function test_csp_can_be_switched_off_from_env(): void
    {
        config(['shop.csp' => false]);

        $this->assertNull($this->get('/')->headers->get('Content-Security-Policy'));
    }

    public function test_account_and_admin_pages_are_never_cached(): void
    {
        $this->assertStringContainsString('no-store', (string) $this->actingAs($this->makeUser())->get('/orders')->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $this->actingAs($this->makeAdmin())->get('/admin')->headers->get('Cache-Control'));
    }

    public function test_new_admins_need_a_strong_password(): void
    {
        $this->actingAs($this->makeAdmin());
        $payload = fn (string $pw) => ['name' => 'New Admin', 'email' => 'new@example.com', 'phone' => '01711111111', 'password' => $pw, 'password_confirmation' => $pw];

        foreach (['shortAa1', 'alllowercase123', 'NOLOWERCASE123', 'NoNumbersHereAtAll'] as $weak) {
            $this->post('/admin/admins', $payload($weak))->assertSessionHasErrors('password');
        }
        $this->assertSame(0, User::where('email', 'new@example.com')->count());

        $this->post('/admin/admins', $payload('Str0ngPassw0rd'))->assertSessionHasNoErrors();
        $this->assertSame(1, User::where('email', 'new@example.com')->count());
    }

    public function test_the_uploads_folder_blocks_scripts(): void
    {
        $file = public_path('images/uploads/.htaccess');

        $this->assertFileExists($file);
        $this->assertStringContainsString('php_flag engine off', file_get_contents($file));
    }

    public function test_deploy_check_flags_admins_with_well_known_passwords(): void
    {
        $this->makeUser('weak@example.com', 'admin', 'Password123');

        $this->artisan('shop:check')->expectsOutputToContain('weak@example.com')->assertExitCode(1);
    }
}
