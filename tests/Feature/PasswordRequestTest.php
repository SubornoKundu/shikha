<?php

namespace Tests\Feature;

use App\Models\PasswordRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordRequestTest extends TestCase
{
    use Concerns, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function customer(string $phone = '01712345678')
    {
        $user = $this->makeUser();
        $user->forceFill(['phone' => $phone])->save();

        return $user;
    }

    private function payload(array $over = []): array
    {
        return array_merge(['email' => 'buyer@example.com', 'phone' => '01712345678', 'password' => 'BrandNew99', 'password_confirmation' => 'BrandNew99'], $over);
    }

    public function test_matching_email_and_phone_creates_a_request_and_does_not_change_the_password(): void
    {
        $user = $this->customer();

        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', $this->payload(['phone' => '+880 1712-345678', 'email' => 'Buyer@Example.com']))
            ->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame(1, PasswordRequest::where('status', 'pending')->count());
        $this->assertTrue(Hash::check('Secret@123', $user->fresh()->password));
    }

    public function test_a_phone_that_does_not_match_the_email_is_refused(): void
    {
        $this->customer();

        $this->post('/forgot-password', $this->payload(['phone' => '01899999999']))->assertSessionHasErrors('phone');
        $this->post('/forgot-password', $this->payload(['email' => 'nobody@example.com']))->assertSessionHasErrors('phone');
        $this->assertSame(0, PasswordRequest::count());
    }

    public function test_admin_accounts_cannot_use_the_request_form(): void
    {
        $admin = $this->makeAdmin();
        $admin->forceFill(['phone' => '01712345678'])->save();

        $this->post('/forgot-password', $this->payload(['email' => 'boss@example.com']))->assertSessionHasErrors('phone');
    }

    public function test_a_new_request_replaces_the_open_one(): void
    {
        $this->customer();
        $this->post('/forgot-password', $this->payload());
        $this->post('/forgot-password', $this->payload(['password' => 'Another99x', 'password_confirmation' => 'Another99x']));

        $this->assertSame(1, PasswordRequest::count());
    }

    public function test_admin_approves_and_the_chosen_password_works(): void
    {
        $user = $this->customer();
        $this->post('/forgot-password', $this->payload());
        $request = PasswordRequest::firstOrFail();

        $this->actingAs($this->makeUser('customer2@example.com'))->patch("/admin/password-requests/{$request->id}/approve")->assertForbidden();
        $this->assertTrue(Hash::check('Secret@123', $user->fresh()->password));

        $this->actingAs($this->makeAdmin())->get('/admin/password-requests')->assertOk();
        $this->patch("/admin/password-requests/{$request->id}/approve")->assertSessionHas('success');

        $this->assertTrue(Hash::check('BrandNew99', $user->fresh()->password));
        $this->assertSame('approved', $request->fresh()->status);
        $this->patch("/admin/password-requests/{$request->id}/approve")->assertNotFound();
    }

    public function test_admin_can_reject_a_request(): void
    {
        $user = $this->customer();
        $this->post('/forgot-password', $this->payload());
        $request = PasswordRequest::firstOrFail();

        $this->actingAs($this->makeAdmin())->patch("/admin/password-requests/{$request->id}/reject")->assertSessionHas('success');

        $this->assertTrue(Hash::check('Secret@123', $user->fresh()->password));
        $this->assertSame('rejected', $request->fresh()->status);
    }

    public function test_admin_can_set_a_customers_password_by_hand(): void
    {
        $user = $this->customer();
        $this->post('/forgot-password', $this->payload());

        $this->actingAs($this->makeAdmin())->patch("/admin/customers/{$user->id}/password", ['password' => 'short'])->assertSessionHasErrors('password');
        $this->patch("/admin/customers/{$user->id}/password", ['password' => 'ChosenByAdmin1'])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('ChosenByAdmin1', $user->fresh()->password));
        $this->assertSame(0, PasswordRequest::where('status', 'pending')->count());
    }
}
