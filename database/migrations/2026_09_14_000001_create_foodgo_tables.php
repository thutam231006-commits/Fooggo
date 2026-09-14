<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('is_available')->default(true);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('ordered_at');
            $table->string('pickup_slot');
            $table->decimal('total', 12, 2);
            $table->string('status')->default('pending_payment');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('method')->default('foodgo_wallet');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('successful');
            $table->dateTime('paid_at')->nullable();
            $table->timestamps();
        });
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('content')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'food_id']);
        });
    }

    public function down(): void
    {
        foreach (['reviews', 'payments', 'order_items', 'orders', 'foods'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
