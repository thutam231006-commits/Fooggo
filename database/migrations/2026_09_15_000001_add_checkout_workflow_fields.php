<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cart_items', 'options')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->json('options')->nullable()->after('quantity'));
        }
        if (! Schema::hasColumn('cart_items', 'unit_price')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->decimal('unit_price', 12, 2)->nullable()->after('options'));
        }
        if (! Schema::hasColumn('cart_items', 'option_signature')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->string('option_signature', 64)->nullable()->after('unit_price'));
        }

        DB::table('cart_items')->whereNull('option_signature')->update(['option_signature' => 'default']);

        if (! Schema::hasIndex('cart_items', 'cart_items_cart_id_index')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->index('cart_id'));
        }
        if (Schema::hasIndex('cart_items', 'cart_items_cart_id_food_id_unique')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->dropUnique(['cart_id', 'food_id']));
        }
        if (! Schema::hasIndex('cart_items', 'cart_food_options_unique')) {
            Schema::table('cart_items', fn (Blueprint $table) => $table->unique(['cart_id', 'food_id', 'option_signature'], 'cart_food_options_unique'));
        }

        if (! Schema::hasColumn('order_items', 'options')) {
            Schema::table('order_items', fn (Blueprint $table) => $table->json('options')->nullable()->after('quantity'));
        }

        $orderColumns = [
            'pickup_code' => fn (Blueprint $table) => $table->string('pickup_code')->nullable()->unique()->after('pickup_slot'),
            'fulfillment_type' => fn (Blueprint $table) => $table->string('fulfillment_type')->default('dine_in')->after('pickup_code'),
            'payment_method' => fn (Blueprint $table) => $table->string('payment_method')->default('foodgo_wallet')->after('fulfillment_type'),
            'service_fee' => fn (Blueprint $table) => $table->decimal('service_fee', 12, 2)->default(0)->after('payment_method'),
            'expires_at' => fn (Blueprint $table) => $table->dateTime('expires_at')->nullable()->after('status'),
            'prepared_at' => fn (Blueprint $table) => $table->dateTime('prepared_at')->nullable()->after('expires_at'),
            'ready_at' => fn (Blueprint $table) => $table->dateTime('ready_at')->nullable()->after('prepared_at'),
            'completed_at' => fn (Blueprint $table) => $table->dateTime('completed_at')->nullable()->after('ready_at'),
        ];
        foreach ($orderColumns as $column => $definition) {
            if (! Schema::hasColumn('orders', $column)) {
                Schema::table('orders', $definition);
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['pickup_code']);
            $table->dropColumn(['pickup_code', 'fulfillment_type', 'payment_method', 'service_fee', 'expires_at', 'prepared_at', 'ready_at', 'completed_at']);
        });
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('options'));
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_food_options_unique');
            $table->dropColumn(['options', 'unit_price', 'option_signature']);
            $table->unique(['cart_id', 'food_id']);
        });
    }
};
