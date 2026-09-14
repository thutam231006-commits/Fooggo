<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_rejects_duplicate_email_and_student_id(): void
    {
        User::factory()->create(['email' => 'student@example.test', 'student_id' => 'SV001']);

        $this->post('/register', [
            'name' => 'Sinh viên mới',
            'email' => 'student@example.test',
            'student_id' => 'SV001',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors(['email', 'student_id']);
    }

    public function test_cart_rejects_unavailable_food_and_quantity_above_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $unavailable = Food::factory()->create(['is_available' => false, 'stock' => 10]);
        $limited = Food::factory()->create(['stock' => 2]);

        $this->actingAs($customer)->post('/cart/items', [
            'food_id' => $unavailable->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('quantity');

        $this->post('/cart/items', [
            'food_id' => $limited->id,
            'quantity' => 3,
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_order_requires_non_empty_cart_and_future_pickup_slot(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->post('/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('cart');

        $this->post('/orders', [
            'pickup_slot' => now()->subHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('pickup_slot');
    }

    public function test_order_rechecks_stock_before_creation_and_preserves_cart_on_failure(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['stock' => 2]);
        $this->actingAs($customer)->post('/cart/items', ['food_id' => $food->id, 'quantity' => 2]);
        $food->update(['stock' => 1]);

        $this->post('/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['food_id' => $food->id, 'quantity' => 2]);
    }

    public function test_cart_clearly_marks_food_that_sells_out_before_ordering(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['name' => 'Cơm gà', 'stock' => 1]);
        $this->actingAs($customer)->post('/cart/items', ['food_id' => $food->id, 'quantity' => 1]);
        $food->update(['stock' => 0]);

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Cơm gà')
            ->assertSee('Hết sản phẩm')
            ->assertSee('Chưa thể đặt hàng');

        $this->from('/cart')->post('/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertRedirect('/cart')->assertSessionHasErrors([
            'cart' => 'Cơm gà - Hết sản phẩm. Vui lòng xóa món khỏi giỏ hàng.',
        ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', ['food_id' => $food->id, 'quantity' => 1]);
    }

    public function test_payment_rejects_insufficient_balance_without_creating_transaction(): void
    {
        [$customer, $order] = $this->pendingOrder(wallet: 10000, total: 30000);

        $this->actingAs($customer)->post("/orders/{$order->id}/payment")
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending_payment']);
        $this->assertDatabaseCount('payments', 0);
        $this->assertEquals('10000.00', $customer->fresh()->wallet_balance);
    }

    public function test_order_cannot_be_paid_twice(): void
    {
        [$customer, $order] = $this->pendingOrder(wallet: 100000, total: 30000);

        $this->actingAs($customer)->post("/orders/{$order->id}/payment")->assertRedirect();
        $this->post("/orders/{$order->id}/payment")->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payments', 1);
        $this->assertEquals('70000.00', $customer->fresh()->wallet_balance);
    }

    public function test_order_cannot_be_paid_after_pickup_time(): void
    {
        [$customer, $order] = $this->pendingOrder(wallet: 100000, total: 30000, pickupSlot: now()->subMinute()->format('Y-m-d H:i'));

        $this->actingAs($customer)->post("/orders/{$order->id}/payment")
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_staff_cannot_skip_order_status_steps(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $staff = User::factory()->create(['role' => 'staff']);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 10000,
            'status' => 'paid',
        ]);

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}/status", [
            'status' => 'completed',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'paid']);
    }

    public function test_admin_api_cannot_downgrade_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'api_token' => 'admin-token']);

        $this->withToken('admin-token')->patchJson("/api/v1/users/{$admin->id}", [
            'role' => 'customer',
        ])->assertUnprocessable()->assertJsonValidationErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }

    public function test_protected_api_endpoints_reject_unauthenticated_requests(): void
    {
        $this->getJson('/api/v1/cart')->assertUnauthorized();
        $this->getJson('/api/v1/orders')->assertUnauthorized();
        $this->getJson('/api/v1/users')->assertUnauthorized();
        $this->getJson('/api/v1/reports/revenue')->assertUnauthorized();
    }

    public function test_public_menu_hides_unavailable_food(): void
    {
        $available = Food::factory()->create(['name' => 'Món đang bán', 'is_available' => true]);
        $unavailable = Food::factory()->create(['name' => 'Món đã ngừng bán', 'is_available' => false]);

        $this->get('/')->assertOk()->assertSee($available->name)->assertDontSee($unavailable->name);
        $this->get("/foods/{$unavailable->id}")->assertNotFound();
    }

    public function test_public_menu_labels_zero_stock_food_as_sold_out(): void
    {
        $food = Food::factory()->create(['name' => 'Món hết kho', 'stock' => 0, 'is_available' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee($food->name)
            ->assertSee('Hết sản phẩm');

        $this->get("/foods/{$food->id}")
            ->assertOk()
            ->assertSee('Hết sản phẩm')
            ->assertDontSee('Thêm vào giỏ');
    }

    public function test_api_login_is_rate_limited_after_repeated_failures(): void
    {
        User::factory()->create(['email' => 'rate-limit@foodgo.test']);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'login' => 'rate-limit@foodgo.test',
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/api/v1/auth/login', [
            'login' => 'rate-limit@foodgo.test',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    private function pendingOrder(int $wallet, int $total, ?string $pickupSlot = null): array
    {
        $customer = User::factory()->create(['role' => 'customer', 'wallet_balance' => $wallet]);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => $pickupSlot ?? now()->addHour()->format('Y-m-d H:i'),
            'total' => $total,
            'status' => 'pending_payment',
        ]);

        return [$customer, $order];
    }
}
