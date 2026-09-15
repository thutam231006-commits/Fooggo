<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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
        $this->post("/orders/{$order->id}/payment")->assertRedirect();

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

    public function test_expired_payment_reservation_releases_stock_and_cancels_order(): void
    {
        [$customer, $order] = $this->pendingOrder(wallet: 100000, total: 30000);
        $food = Food::factory()->create(['stock' => 3]);
        $order->update(['payment_expires_at' => now()->subMinute()]);
        $order->items()->create(['food_id' => $food->id, 'quantity' => 2, 'unit_price' => 15000, 'subtotal' => 30000]);
        $food->decrement('stock', 2);

        $this->actingAs($customer)->post("/orders/{$order->id}/payment")
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 3]);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_scheduled_expiry_releases_stock_only_once(): void
    {
        [$customer, $order] = $this->pendingOrder(wallet: 100000, total: 30000);
        $food = Food::factory()->create(['stock' => 1]);
        $order->update(['payment_expires_at' => now()->subMinute()]);
        $order->items()->create(['food_id' => $food->id, 'quantity' => 2, 'unit_price' => 15000, 'subtotal' => 30000]);

        $this->artisan('orders:expire')->assertSuccessful();
        $this->artisan('orders:expire')->assertSuccessful();
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 3]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertEquals('100000.00', $customer->fresh()->wallet_balance);
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

    public function test_customized_food_price_and_options_are_preserved_in_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['price' => 30000, 'stock' => 10]);

        $this->actingAs($customer)->post('/cart/items', [
            'food_id' => $food->id,
            'quantity' => 2,
            'rice_type' => 'garlic',
            'extras' => ['egg', 'soup'],
            'sauce' => 'spicy',
            'spice_level' => 'medium',
            'note' => 'Không hành',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('cart_items', ['food_id' => $food->id, 'unit_price' => 48000]);

        $this->post('/orders', ['pickup_slot' => now()->addHour()->format('Y-m-d\TH:i')])->assertSessionHasNoErrors();
        $order = Order::latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame('96000.00', $order->total);
        $this->assertSame('48000.00', $item->unit_price);
        $this->assertSame(['egg', 'soup'], $item->options['extras']);
        $this->assertSame('Không hành', $item->options['note']);
        $this->assertNotNull($order->pickup_code);
        $this->assertNotNull($order->expires_at);
    }

    public function test_same_food_with_different_customizations_creates_separate_cart_lines(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['price' => 30000, 'stock' => 10]);

        $this->actingAs($customer)->post('/cart/items', ['food_id' => $food->id, 'quantity' => 1]);
        $this->post('/cart/items', ['food_id' => $food->id, 'quantity' => 1, 'extras' => ['egg']]);

        $this->assertDatabaseCount('cart_items', 2);
        $this->assertEqualsCanonicalizing([30000.0, 37000.0], $customer->cart->items->pluck('unit_price')->map(fn ($price) => (float) $price)->all());
    }

    public function test_total_quantity_across_customizations_cannot_exceed_food_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['stock' => 2]);

        $this->actingAs($customer)->post('/cart/items', ['food_id' => $food->id, 'quantity' => 2])->assertSessionHasNoErrors();
        $this->post('/cart/items', ['food_id' => $food->id, 'quantity' => 1, 'extras' => ['egg']])->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(2, (int) $customer->cart->items()->sum('quantity'));
    }

    public function test_expired_pending_order_releases_reserved_stock(): void
    {
        $food = Food::factory()->create(['stock' => 8, 'price' => 20000]);
        [$customer, $order] = $this->pendingOrder(wallet: 100000, total: 40000);
        $order->items()->create(['food_id' => $food->id, 'quantity' => 2, 'unit_price' => 20000, 'subtotal' => 40000]);
        $order->update(['expires_at' => now()->subMinute()]);

        $expired = app(OrderWorkflowService::class)->expirePendingOrders();

        $this->assertSame(1, $expired);
        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame(10, $food->fresh()->stock);
    }

    public function test_customer_can_cancel_paid_order_before_kitchen_accepts_and_receive_refund(): void
    {
        $food = Food::factory()->create(['stock' => 8, 'price' => 30000]);
        [$customer, $order] = $this->pendingOrder(wallet: 70000, total: 30000);
        $order->items()->create(['food_id' => $food->id, 'quantity' => 1, 'unit_price' => 30000, 'subtotal' => 30000]);
        $order->payment()->create(['method' => 'foodgo_wallet', 'amount' => 30000, 'status' => 'successful', 'paid_at' => now()]);
        $order->update(['status' => 'paid']);

        $this->actingAs($customer)->delete("/orders/{$order->id}")->assertSessionHasNoErrors();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertSame('refunded', $order->payment->fresh()->status);
        $this->assertSame('100000.00', $customer->fresh()->wallet_balance);
        $this->assertSame(9, $food->fresh()->stock);
    }

    public function test_pickup_verification_requires_a_valid_temporary_signature(): void
    {
        [, $order] = $this->pendingOrder(wallet: 100000, total: 30000);
        $order->update(['pickup_code' => 'A-123']);
        $signedUrl = URL::temporarySignedRoute('pickup.verify', now()->addHour(), ['order' => $order]);

        $this->get($signedUrl)->assertOk()->assertSee('A-123');
        $this->get(route('pickup.verify', $order))->assertForbidden();
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
