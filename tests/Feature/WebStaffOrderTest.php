<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebStaffOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_unpaid_order_is_visible_to_staff_but_cannot_be_processed(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $staff = User::factory()->create(['role' => 'staff']);
        $food = Food::factory()->create(['name' => 'Cơm gà']);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => $food->price,
            'status' => 'pending_payment',
        ]);
        $order->items()->create([
            'food_id' => $food->id,
            'quantity' => 1,
            'unit_price' => $food->price,
            'subtotal' => $food->price,
        ]);

        $this->actingAs($staff)
            ->get('/staff/dashboard')
            ->assertOk()
            ->assertSee("#{$order->id}")
            ->assertSee('Cơm gà')
            ->assertSee('Chờ khách thanh toán')
            ->assertSee('Chưa thể xử lý')
            ->assertDontSee('Nhận đơn');

        $this->patch("/staff/orders/{$order->id}/status", [
            'status' => 'preparing',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending_payment']);
    }

    public function test_staff_can_reject_paid_order_with_reason_and_customer_is_refunded(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
            'wallet_balance' => 60000,
        ]);
        $staff = User::factory()->create(['role' => 'staff']);
        $food = Food::factory()->create(['stock' => 8, 'price' => 20000]);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 40000,
            'status' => 'paid',
        ]);
        $order->items()->create([
            'food_id' => $food->id,
            'quantity' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
        ]);
        $order->payment()->create([
            'transaction_code' => 'FG-TEST-REJECT',
            'method' => 'foodgo_wallet',
            'amount' => 40000,
            'status' => 'successful',
            'paid_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get('/staff/dashboard')
            ->assertOk()
            ->assertSee('Nhận đơn')
            ->assertSee('Từ chối đơn')
            ->assertSee('Nhập lý do từ chối');

        $this->from('/staff/dashboard')->patch("/staff/orders/{$order->id}/status", [
            'status' => 'rejected',
        ])->assertRedirect('/staff/dashboard')->assertSessionHasErrors('rejection_reason');

        $this->patch("/staff/orders/{$order->id}/status", [
            'status' => 'rejected',
            'rejection_reason' => 'Căn tin đã hết nguyên liệu.',
        ])->assertRedirect('/staff/dashboard')->assertSessionHas('status');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'rejected',
            'processed_by' => $staff->id,
            'rejection_reason' => 'Căn tin đã hết nguyên liệu.',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'refunded',
        ]);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'stock' => 10]);
        $this->assertEquals('100000.00', $customer->fresh()->wallet_balance);
    }

    public function test_customer_cannot_use_staff_order_status_route(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 10000,
            'status' => 'paid',
        ]);

        $this->actingAs($customer)->patch("/staff/orders/{$order->id}/status", [
            'status' => 'rejected',
            'rejection_reason' => 'Không hợp lệ.',
        ])->assertForbidden();
    }

    public function test_staff_can_progress_order_through_all_fulfillment_states(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $staff = User::factory()->create(['role' => 'staff']);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 50000,
            'status' => 'paid',
        ]);

        $this->actingAs($staff)->patch("/staff/orders/{$order->id}/status", ['status' => 'preparing'])->assertRedirect('/staff/dashboard');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'preparing', 'processed_by' => $staff->id]);

        $this->patch("/staff/orders/{$order->id}/status", ['status' => 'ready'])->assertRedirect('/staff/dashboard');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'ready']);

        $this->patch("/staff/orders/{$order->id}/status", ['status' => 'completed'])->assertRedirect('/staff/dashboard');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }
}
