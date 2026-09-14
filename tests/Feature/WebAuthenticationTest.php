<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class WebAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_then_login_with_session(): void
    {
        $this->post('/register', [
            'name' => 'Nguyễn Văn An',
            'email' => 'an@example.test',
            'student_id' => 'SV12345',
            'phone' => '0901234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/login');

        $this->assertDatabaseHas('users', [
            'email' => 'an@example.test',
            'role' => 'customer',
        ]);

        $this->post('/login', [
            'login' => 'SV12345',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->get('/dashboard')->assertRedirect('/customer/dashboard');
        $this->get('/customer/dashboard')->assertOk()->assertSee('Nguyễn Văn An');
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'locked@example.test',
            'password' => 'password123',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'login' => 'locked@example.test',
            'password' => 'password123',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_each_role_is_redirected_to_its_own_dashboard(): void
    {
        foreach (['customer', 'staff', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/dashboard')->assertRedirect("/{$role}/dashboard");
        }
    }

    public function test_customer_cannot_access_staff_or_admin_dashboards(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get('/staff/dashboard')->assertForbidden();
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_navigation_uses_consistent_authentication_actions(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Đăng nhập')
            ->assertSee('Đăng ký')
            ->assertDontSee('Đăng xuất');

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer)
            ->get('/customer/dashboard')
            ->assertOk()
            ->assertSee('Đăng xuất')
            ->assertDontSee('Đăng ký');

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_expired_csrf_session_redirects_to_login_instead_of_showing_419_page(): void
    {
        $request = Request::create('/logout', 'POST');
        $request->setLaravelSession($this->app['session']->driver());

        $response = $this->app->make(ExceptionHandler::class)
            ->render($request, new TokenMismatchException);

        $this->assertSame(route('login'), $response->headers->get('Location'));
    }
}
