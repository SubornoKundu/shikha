<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use Concerns, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_pages_render(): void
    {
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
        $this->get('/forgot-password')->assertOk();
    }

    public function test_register_creates_verified_customer_and_logs_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Person', 'email' => 'New@Example.com', 'phone' => '01712345678',
            'password' => 'Password1', 'password_confirmation' => 'Password1',
        ]);

        $response->assertRedirect('/');
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('customer', $user->role);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue(Hash::check('Password1', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_register_validation(): void
    {
        $this->makeUser('taken@example.com');

        $this->post('/register', ['name' => '', 'email' => 'taken@example.com', 'password' => 'short', 'password_confirmation' => 'nope'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertGuest();
    }

    public function test_cannot_self_assign_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky', 'email' => 'sneaky@example.com', 'role' => 'admin',
            'password' => 'Password1', 'password_confirmation' => 'Password1',
        ]);

        $this->assertSame('customer', User::where('email', 'sneaky@example.com')->value('role'));
    }

    public function test_login_logout(): void
    {
        $user = $this->makeUser();

        $this->post('/login', ['email' => 'buyer@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'BUYER@example.com', 'password' => 'Secret@123'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_admin_login_goes_to_admin_panel(): void
    {
        $this->makeAdmin();

        $this->post('/admin/login', ['email' => 'boss@example.com', 'password' => 'Secret@123'])->assertRedirect('/admin');
        $this->assertAuthenticated();
    }

    public function test_admins_cannot_sign_in_on_the_customer_page_and_customers_not_on_the_admin_page(): void
    {
        $this->makeAdmin();
        $this->makeUser();

        $this->post('/login', ['email' => 'boss@example.com', 'password' => 'Secret@123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/admin/login', ['email' => 'buyer@example.com', 'password' => 'Secret@123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->get('/admin/login')->assertOk();
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_blocked_user_cannot_login_and_is_kicked_out(): void
    {
        $user = $this->makeUser();
        $user->forceFill(['is_blocked' => true])->save();

        $this->post('/login', ['email' => 'buyer@example.com', 'password' => 'Secret@123'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $user->forceFill(['is_blocked' => false])->save();
        $this->actingAs($user)->get('/profile')->assertOk();

        $user->forceFill(['is_blocked' => true])->save();
        $this->actingAs($user)->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'buyer@example.com', 'password' => 'bad']);
        }

        $this->post('/login', ['email' => 'buyer@example.com', 'password' => 'Secret@123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_profile_and_password_update(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->put('/profile', ['name' => 'Renamed', 'phone' => '01799999999', 'address' => 'Road 9', 'city' => 'Sylhet'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Sylhet', $user->fresh()->city);
        $this->assertSame('customer', $user->fresh()->role);

        $this->put('/profile/password', ['current_password' => 'nope', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123'])
            ->assertSessionHasErrors('current_password');

        $this->put('/profile/password', ['current_password' => 'Secret@123', 'password' => 'NewPass123', 'password_confirmation' => 'NewPass123'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }
}
