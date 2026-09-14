<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'processed_by')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('processed_by')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('payments', 'transaction_code')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('transaction_code')->nullable()->unique()->after('order_id');
            });
        }

        if (! Schema::hasColumn('payments', 'refunded_at')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dateTime('refunded_at')->nullable()->after('paid_at');
            });
        }

        if (! Schema::hasColumn('reviews', 'order_id')) {
            Schema::table('reviews', function (Blueprint $table) {
                // MySQL may use the old composite unique index for this foreign key.
                $table->index('user_id', 'reviews_user_id_index');
                $table->dropUnique(['user_id', 'food_id']);
                $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
                $table->unique(['user_id', 'order_id', 'food_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'order_id', 'food_id']);
            $table->dropConstrainedForeignId('order_id');
            $table->dropIndex('reviews_user_id_index');
            $table->unique(['user_id', 'food_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['transaction_code']);
            $table->dropColumn(['transaction_code', 'refunded_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('processed_by');
        });
    }
};
