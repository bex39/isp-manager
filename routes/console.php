<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Generate invoices setiap tanggal 1 jam 00:00 WITA (Asia/Makassar)
Schedule::command('invoices:generate-monthly')
    ->monthlyOn(1, '00:00')
    ->timezone('Asia/Makassar');

// Send reminders setiap hari jam 09:00
Schedule::command('invoices:send-reminders')
    ->dailyAt('09:00')
    ->timezone('Asia/Makassar');

// Auto suspend overdue customers
Schedule::command('customers:auto-suspend')
    ->dailyAt('10:00')
    ->timezone('Asia/Makassar');

// Check router uptime (setiap 5 menit)
Schedule::command('routers:check-uptime')
    ->everyFiveMinutes();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


