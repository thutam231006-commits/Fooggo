<?php

use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('orders:expire', function () {
    $count = app(OrderWorkflowService::class)->expireReservations();
    $this->info("Expired reservations: {$count}");
})->purpose('Release stock held by unpaid expired orders');

Schedule::command('orders:expire')->everyMinute()->withoutOverlapping();
