<?php

namespace Database\Factories;

use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;

class FoodFactory extends Factory
{
    protected $model = Food::class;

    public function definition(): array
    {
        return ['name' => fake()->randomElement(['Cơm sườn nướng', 'Bún thịt nướng', 'Mì xào bò', 'Bánh mì thịt']), 'category' => fake()->randomElement(['Cơm', 'Mì', 'Đồ uống', 'Ăn vặt']), 'description' => 'Món ăn căn tin được chế biến trong ngày.', 'price' => fake()->numberBetween(15, 60) * 1000, 'stock' => fake()->numberBetween(10, 100), 'is_available' => true];
    }
}
