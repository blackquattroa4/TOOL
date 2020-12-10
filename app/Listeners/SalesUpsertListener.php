<?php

namespace App\Listeners;

use App\Events\SalesUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class SalesUpsertListener implements ShouldQueue
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
     * @param  SalesUpsertEvent  $event
     * @return void
     */
    public function handle(SalesUpsertEvent $event)
    {
        Log::info('sales #' . $event->sales->id . ' up-serted');
    }
}
