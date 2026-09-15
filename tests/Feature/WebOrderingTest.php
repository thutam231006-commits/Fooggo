<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_menu_is_the_default_start_page_and_uses_compact_pagination(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        Food::factory()->count(10)->create();

        $this->actingAs($customer)->get('/dashboard')->assertRedirect('/');
        $this->get('/')->assertOk()->assertViewHas('foods', fn ($foods) => $foods->count() === 8);
    }

    public function test_customer_can_search_menu_and_add_item_without_leaving_menu(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $target = Food::factory()->create(['name' => 'Cơm cá kho đặc biệt', 'stock' => 10]);
        Food::factory()->create(['name' => 'Bún thịt nướng']);

        $this->actingAs($customer)->get('/?search=cá+kho')->assertOk()->assertSee($target->name)->assertDontSee('Bún thịt nướng');
        $this->post('/cart/items', ['food_id' => $target->id, 'quantity' => 1, 'redirect_to' => 'menu'])->assertRedirect('/');
    }

    public function test_customer_can_add_food_create_order_and_pay_on_web(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'wallet_balance' => 100000,
        ]);
        $food = Food::factory()->create([
            'price' => 30000,
            'stock' => 10,
        ]);

        $this->actingAs($customer)
            ->get('/')
            ->assertOk()
            ->assertSee('Thêm vào giỏ')
            ->assertSee('/cart/items', false);

        $this->actingAs($customer)
            ->get("/foods/{$food->id}")
            ->assertOk()
            ->assertSee('Thêm vào giỏ');

        $this->actingAs($customer)
            ->post('/cart/items', ['food_id' => $food->id, 'quantity' => 2])
            ->assertRedirect('/cart');

        $this->get('/cart')
            ->assertOk()
            ->assertSee($food->name)
            ->assertSee('60.000 đ');

        $orderLocation = $this->from('/cart')->post('/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d\TH:i'),
        ])->assertSessionHasNoErrors()->assertRedirect()->headers->get('Location');

        $orderId = basename($orderLocation);
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'user_id' => $customer->id,
            'status' => 'pending_payment',
            'total' => 60000,
        ]);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 8]);
        $this->assertDatabaseCount('cart_items', 0);

        $this->post("/orders/{$orderId}/payment")
            ->assertRedirect("/orders/{$orderId}");

        $this->assertDatabaseHas('orders', ['id' => $orderId, 'status' => 'paid']);
        $this->assertDatabaseHas('payments', ['order_id' => $orderId, 'status' => 'successful']);
        $this->assertEquals('40000.00', $customer->fresh()->wallet_balance);
    }

    public function test_customer_can_keep_shopping_and_cart_preserves_multiple_foods(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $rice = Food::factory()->create(['name' => 'Cơm gà', 'price' => 30000, 'stock' => 10]);
        $drink = Food::factory()->create(['name' => 'Trà tắc', 'price' => 15000, 'stock' => 10]);

        $this->actingAs($customer)->post('/cart/items', [
            'food_id' => $rice->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Tiếp tục chọn món')
            ->assertSee(route('home'), false);

        $this->post('/cart/items', [
            'food_id' => $drink->id,
            'quantity' => 2,
        ])->assertRedirect('/cart');

        $this->assertDatabaseCount('cart_items', 2);
        $this->assertDatabaseHas('cart_items', ['food_id' => $rice->id, 'quantity' => 1]);
        $this->assertDatabaseHas('cart_items', ['food_id' => $drink->id, 'quantity' => 2]);

        $this->get('/cart')
            ->assertOk()
            ->assertSee('Cơm gà')
            ->assertSee('Trà tắc')
            ->assertSee('60.000 đ');
    }

    public function test_customer_can_place_order_with_cash_on_delivery_and_payment_is_completed_on_pickup(): void
    {
        $customer = User::factory()->create(['role' => 'customer', 'wallet_balance' => 0]);
        $staff = User::factory()->create(['role' => 'staff']);
        $food = Food::factory()->create(['price' => 25000, 'stock' => 5]);

        $this->actingAs($customer)->post('/cart/items', [
            'food_id' => $food->id,
            'quantity' => 1,
        ])->assertRedirect('/cart');
        $this->get('/cart')->assertOk()->assertSee('cash_on_delivery', false);

        $location = $this->post('/orders', [
            'pickup_slot' => now()->addHour()->format('Y-m-d\\TH:i'),
            'payment_method' => 'cash_on_delivery',
        ])->assertSessionHasNoErrors()->assertRedirect()->headers->get('Location');

        $orderId = basename($location);
        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'paid',
            'payment_method' => 'cash_on_delivery',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'method' => 'cash_on_delivery',
            'status' => 'pending',
        ]);

        $this->actingAs($staff)->patch("/staff/orders/{$orderId}/status", ['status' => 'preparing'])->assertRedirect('/staff/dashboard');
        $this->patch("/staff/orders/{$orderId}/status", ['status' => 'ready'])->assertRedirect('/staff/dashboard');
        $this->patch("/staff/orders/{$orderId}/status", ['status' => 'completed'])->assertRedirect('/staff/dashboard');

        $this->assertDatabaseHas('payments', [
            'order_id' => $orderId,
            'method' => 'cash_on_delivery',
            'status' => 'successful',
        ]);
        $this->assertSame('0.00', $customer->fresh()->wallet_balance);
    }

    public function test_staff_cannot_open_customer_cart(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get('/cart')->assertForbidden();
    }

    public function test_empty_cart_shows_choose_food_and_disabled_order_action(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get('/cart')
            ->assertOk()
            ->assertSee('Chọn món')
            ->assertSee('Đặt hàng');
    }

    public function test_customer_can_cancel_unpaid_order_and_stock_is_restored(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['stock' => 8, 'price' => 20000]);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 40000,
            'status' => 'pending_payment',
        ]);
        $order->items()->create([
            'food_id' => $food->id,
            'quantity' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
        ]);

        $this->actingAs($customer)
            ->delete("/orders/{$order->id}")
            ->assertRedirect("/orders/{$order->id}");

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 10]);
    }

    public function test_dashboard_shows_cancel_action_for_unpaid_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 40000,
            'status' => 'pending_payment',
        ]);

        $this->actingAs($customer)
            ->get('/customer/dashboard')
            ->assertOk()
            ->assertSee('Hủy đơn')
            ->assertSee(route('web.orders.cancel', $order), false);

        $this->delete("/orders/{$order->id}", ['redirect_to' => 'dashboard'])
            ->assertRedirect('/customer/dashboard')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_customer_can_review_food_from_completed_order_once(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['name' => 'Cơm gà']);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->subHour()->format('Y-m-d H:i'),
            'total' => $food->price,
            'status' => 'completed',
        ]);
        $order->items()->create([
            'food_id' => $food->id,
            'quantity' => 1,
            'unit_price' => $food->price,
            'subtotal' => $food->price,
        ]);

        $this->actingAs($customer)
            ->get("/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Gửi đánh giá');

        $this->post("/orders/{$order->id}/foods/{$food->id}/review", [
            'rating' => 5,
            'content' => 'Món ngon và giao đúng giờ.',
        ])->assertRedirect("/orders/{$order->id}");

        $this->assertDatabaseHas('reviews', [
            'user_id' => $customer->id,
            'order_id' => $order->id,
            'food_id' => $food->id,
            'rating' => 5,
        ]);

        $this->from("/orders/{$order->id}")->post("/orders/{$order->id}/foods/{$food->id}/review", [
            'rating' => 4,
        ])->assertSessionHasErrors('review');

        $this->get("/foods/{$food->id}")
            ->assertOk()
            ->assertSee('Món ngon và giao đúng giờ.');
    }
}
