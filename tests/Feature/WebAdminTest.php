<?php

namespace Tests\Feature;

use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_disable_food(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/foods/create')
            ->assertOk()
            ->assertSee('Tạo món ăn');

        $location = $this->post('/admin/foods', [
            'name' => 'Cơm cá kho',
            'category' => 'Cơm',
            'description' => 'Cá kho dùng với cơm nóng.',
            'price' => 35000,
            'stock' => 20,
            'is_available' => 1,
            'image_url' => null,
        ])->assertRedirect()->headers->get('Location');

        $food = Food::where('name', 'Cơm cá kho')->firstOrFail();
        $this->assertStringEndsWith("/admin/foods/{$food->id}/edit", $location);

        $this->put("/admin/foods/{$food->id}", [
            'name' => 'Cơm cá kho tiêu',
            'category' => 'Cơm',
            'description' => 'Cá kho tiêu dùng với cơm nóng.',
            'price' => 38000,
            'stock' => 18,
            'is_available' => 1,
            'image_url' => null,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('foods', ['id' => $food->id, 'name' => 'Cơm cá kho tiêu', 'price' => 38000]);

        $this->delete("/admin/foods/{$food->id}")->assertRedirect('/admin/foods');
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'is_available' => false]);
    }

    public function test_admin_can_manage_another_user_but_cannot_downgrade_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer', 'api_token' => 'legacy-token']);

        $this->actingAs($admin)->put("/admin/users/{$customer->id}", [
            'name' => $customer->name,
            'email' => $customer->email,
            'student_id' => $customer->student_id,
            'phone' => $customer->phone,
            'role' => 'staff',
            'wallet_balance' => 50000,
            'is_active' => 0,
            'password' => null,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => 'staff',
            'wallet_balance' => 50000,
            'is_active' => false,
            'api_token' => null,
        ]);

        $this->from("/admin/users/{$admin->id}/edit")->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'student_id' => null,
            'phone' => null,
            'role' => 'customer',
            'wallet_balance' => 0,
            'is_active' => 1,
            'password' => null,
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_can_create_staff_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Nhân viên mới',
            'email' => 'new-staff@foodgo.test',
            'student_id' => null,
            'phone' => '0909000000',
            'role' => 'staff',
            'wallet_balance' => 0,
            'is_active' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'email' => 'new-staff@foodgo.test',
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_admin_report_contains_completed_revenue_and_best_seller(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $food = Food::factory()->create(['name' => 'Bún bò Huế', 'price' => 40000]);
        $order = Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 80000,
            'status' => 'completed',
        ]);
        $order->items()->create([
            'food_id' => $food->id,
            'quantity' => 2,
            'unit_price' => 40000,
            'subtotal' => 80000,
        ]);

        $this->actingAs($admin)
            ->get('/admin/reports?from='.now()->format('Y-m-d').'&to='.now()->format('Y-m-d'))
            ->assertOk()
            ->assertSee('80.000 đ')
            ->assertSee('Bún bò Huế');
    }

    public function test_customer_cannot_access_admin_management_pages(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get('/admin/foods')->assertForbidden();
        $this->get('/admin/foods/create')->assertForbidden();
        $this->post('/admin/foods', [
            'name' => 'Món trái phép',
            'category' => 'Cơm',
            'price' => 10000,
            'stock' => 10,
            'is_available' => 1,
        ])->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/reports')->assertForbidden();
        $this->assertDatabaseMissing('foods', ['name' => 'Món trái phép']);
    }

    public function test_admin_report_excludes_orders_that_are_not_completed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        Order::create([
            'user_id' => $customer->id,
            'ordered_at' => now(),
            'pickup_slot' => now()->addHour()->format('Y-m-d H:i'),
            'total' => 999000,
            'status' => 'paid',
        ]);

        $this->actingAs($admin)->get('/admin/reports')
            ->assertOk()
            ->assertSee('0 đ')
            ->assertDontSee('999.000 đ');
    }
}
