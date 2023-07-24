<?php

namespace App\Console;

use App\Models\ReactorCycle;
use App\Services\ReactorCycle\ReactorCycleService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    private $reactorCycleService;
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected function schedule(Schedule $schedule)
    {
        $this->reactorCycleService = new ReactorCycleService();
        $schedule->call(
            function() {
                $this->reactorCycleService->updateArchivedStatus();
            }
        )->cron('0 0 * * *');
    }
}
