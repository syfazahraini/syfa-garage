<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/** Skenario 1: login, logout, dan halaman yang butuh login. */
class AuthenticationTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_protected_pages(): void
    {
        foreach (['dashboard', 'products.index', 'sales.index', 'purchases.index', 'users.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $staff = $this->staff();

        $this->post(route('login.attempt'), ['email' => $staff->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($staff);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $staff = $this->staff();

        $this->from(route('login'))
            ->post(route('login.attempt'), ['email' => $staff->email, 'password' => 'salah-total'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post(route('login.attempt'), [])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs($this->staff())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_there_is_no_public_registration(): void
    {
        // Akun hanya dibuat oleh Admin, jadi halaman daftar mandiri tidak ada.
        $this->get('/register')->assertNotFound();
    }
}
