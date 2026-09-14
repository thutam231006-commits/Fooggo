<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodGoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_order_from_cart_and_pay_with_foodgo_wallet(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'wallet_balance' => 100000,
            'api_token' => 'customer-token',
        ]);
        $food = Food::factory()->create(['price' => 30000, 'stock' => 10]);

        $this->withToken('customer-token')->postJson('/api/v1/cart/items', [
            'food_id' => $food->id,
            'quantity' => 2,
        ])->assertCreated();

        $orderId = $this->withToken('customer-token')->postJson('/api/v1/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
        ])->assertCreated()->assertJsonPath('status', 'pending_payment')->json('id');

        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 8]);
        $this->assertDatabaseCount('cart_items', 0);

        $this->withToken('customer-token')->postJson("/api/v1/orders/{$orderId}/payment")
            ->assertOk()
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('payment.status', 'successful');

        $this->assertEquals('40000.00', $customer->fresh()->wallet_balance);
    }

    public function test_rejected_paid_order_refunds_wallet_and_restores_stock(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'wallet_balance' => 100000, 'api_token' => 'customer-token']);
        User::factory()->create(['role' => 'staff', 'api_token' => 'staff-token']);
        $food = Food::factory()->create(['price' => 25000, 'stock' => 5]);

        $orderId = $this->withToken('customer-token')->postJson('/api/v1/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'items' => [['food_id' => $food->id, 'quantity' => 2]],
        ])->assertCreated()->json('id');

        $this->withToken('customer-token')->postJson("/api/v1/orders/{$orderId}/payment")->assertOk();
        $this->withToken('staff-token')->patchJson("/api/v1/orders/{$orderId}/status", [
            'status' => 'rejected',
            'rejection_reason' => 'Căn tin tạm hết nguyên liệu.',
        ])->assertOk()->assertJsonPath('payment.status', 'refunded');

        $this->assertEquals('100000.00', $customer->fresh()->wallet_balance);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 5]);
    }

    public function test_customer_cannot_access_admin_food_crud(): void
    {
        User::factory()->create(['role' => 'customer', 'api_token' => 'customer-token']);

        $this->withToken('customer-token')->postJson('/api/v1/foods', [
            'name' => 'Cơm gà', 'price' => 35000, 'stock' => 10,
        ])->assertForbidden();
    }

    public function test_staff_cannot_use_customer_cart_or_create_orders(): void
    {
        User::factory()->create(['role' => 'staff', 'api_token' => 'staff-token']);
        $food = Food::factory()->create();

        $this->withToken('staff-token')->postJson('/api/v1/cart/items', [
            'food_id' => $food->id,
            'quantity' => 1,
        ])->assertForbidden();

        $this->withToken('staff-token')->postJson('/api/v1/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'items' => [['food_id' => $food->id, 'quantity' => 1]],
        ])->assertForbidden();
    }

    public function test_user_can_login_to_api_with_student_id(): void
    {
        User::factory()->create([
            'student_id' => 'SV2026001',
            'password' => 'secret123',
        ]);

        $token = $this->postJson('/api/v1/auth/login', [
            'login' => 'SV2026001',
            'password' => 'secret123',
        ])->assertOk()->assertJsonStructure(['user', 'token'])->json('token');

        $this->assertNotSame($token, User::where('student_id', 'SV2026001')->value('api_token'));
        $this->assertSame(hash('sha256', $token), User::where('student_id', 'SV2026001')->value('api_token'));
    }

    public function test_customer_can_review_food_once_per_completed_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'api_token' => 'customer-token']);
        $food = Food::factory()->create();
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => $food->price,
            'status' => 'completed',
        ]);
        $order->items()->create([
            'food_id' => $food->id,
            'quantity' => 1,
            'unit_price' => $food->price,
            'subtotal' => $food->price,
        ]);

        $payload = ['order_id' => $order->id, 'rating' => 5, 'content' => 'Món ăn ngon.'];
        $this->withToken('customer-token')->postJson("/api/v1/foods/{$food->id}/reviews", $payload)->assertCreated();
        $this->withToken('customer-token')->postJson("/api/v1/foods/{$food->id}/reviews", $payload)->assertUnprocessable();
    }

    public function test_report_rejects_an_invalid_date_range(): void
    {
        User::factory()->create(['role' => 'admin', 'api_token' => 'admin-token']);

        $this->withToken('admin-token')->getJson('/api/v1/reports/best-sellers?from=2026-09-15&to=2026-09-14')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }
}
