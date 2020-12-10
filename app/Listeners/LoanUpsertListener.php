<?php

namespace App\Listeners;

use App\Events\LoanUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class LoanUpsertListener implements ShouldQueue
{

    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LoanUpsertEvent  $event
     * @return void
     */
    public function handle(LoanUpsertEvent $event)
    {
        Log::info('Loan #' . $event->loan->id . ' up-serted');
    }
}
