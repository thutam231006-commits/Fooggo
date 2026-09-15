<?php

use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:expire', function (OrderWorkflowService $workflow) {
    $count = $workflow->expirePendingOrders();
    $this->info("Đã hết hạn {$count} đơn chờ thanh toán.");
})->purpose('Hoàn tồn kho cho đơn quá hạn thanh toán');

Schedule::command('orders:expire')->everyMinute()->withoutOverlapping();
