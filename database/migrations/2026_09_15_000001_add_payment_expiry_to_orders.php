<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('orders', 'payment_expires_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dateTime('payment_expires_at')->nullable()->after('pickup_slot');
                $table->index(['status', 'payment_expires_at']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'payment_expires_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropIndex(['status', 'payment_expires_at']);
                $table->dropColumn('payment_expires_at');
            });
        }
    }
};
