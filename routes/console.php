<?php

use App\Models\Complaint;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('complaints:escalate', function () {
    $count = Complaint::query()
        ->overdue()
        ->whereNull('escalated_at')
        ->update(['escalated_at' => now()]);

    $this->info("{$count} pengaduan dieskalasi ke kabupaten.");
})->purpose('Tandai pengaduan yang melewati batas SLA sebagai tereskalasi');

Schedule::command('complaints:escalate')->hourly()->withoutOverlapping();
