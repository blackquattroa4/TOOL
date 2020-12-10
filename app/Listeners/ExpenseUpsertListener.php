<?php

namespace App\Listeners;

use App\Events\ExpenseUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class ExpenseUpsertListener implements ShouldQueue
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
     * @param  ExpenseUpsertEvent  $event
     * @return void
     */
    public function handle(ExpenseUpsertEvent $event)
    {
        Log::info('expense #' . $event->expense->id . ' up-serted');
    }
}
