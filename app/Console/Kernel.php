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
        // Commands\Inspire::class,
        // Commands\Tester::class,
        Commands\MissingTranslation::class,
        Commands\OpticalCharacterRecognition::class,
        Commands\CreateIncome::class,
        Commands\CreateExpense::class,
        Commands\CurrencyExchange::class,
        Commands\PusherNotification::class,
        Commands\EcommerceSync::class,
        Commands\CalculateLoanInterest::class,
        Commands\CleanStorage::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        // // create income
        // $schedule->command('')
        //           ->daily();

        // // create expense
        // $schedule->command('')
        //           ->daily();

        $schedule->command('investment:update')
                  ->timezone('America/Los_Angeles')
                  ->cron('45 23 * * 1-5')
                  ->withoutOverlapping()
                  ->onOneServer();

        $schedule->command('storage:declutter 6')
                  ->timezone('America/Los_Angeles')
                  ->cron('30 * * * 1-5')
                  ->withoutOverlapping()
                  ->onOneServer();
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
