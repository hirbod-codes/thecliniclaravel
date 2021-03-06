<?php

namespace App\Console;

use App\Jobs\VisitReminder;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new VisitReminder)->everyMinute();

        $schedule->command('passport:purge')->everyTwoHours();

        $schedule->command('queue:prune-batches')->daily();
        $schedule->command('queue:prune-batches --hours=168 --unfinished=72')->daily();
        $schedule->command('queue:monitor database:default --max:700')->daily();

        $schedule->call(function () {
            /** @var User $user */
            foreach (User::query()
                ->where('phonenumber_verified_at', '=', null)
                ->get() as $user) {
                $user->delete();
            }
        }, [])->everyTwoHours();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
