<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $expiresAt = (int) config('scheduling.overlapping_expires_at');

        $schedule
            ->command('accounts:update_balance')
            ->everyMinute()
            ->withoutOverlapping($expiresAt);

        $schedule
            ->command('account:get-coins')
            ->everyTenMinutes()
            ->withoutOverlapping($expiresAt);

        $schedule
            ->command('coin:get-change-24h')
            ->everyTenMinutes()
            ->withoutOverlapping($expiresAt);

        $schedule
            ->command('account:calculate-profits')
            ->everyFiveMinutes()
            ->withoutOverlapping($expiresAt);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
