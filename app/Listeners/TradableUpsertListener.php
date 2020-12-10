<?php

namespace App\Listeners;

use App\Events\TradableUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class TradableUpsertListener implements ShouldQueue
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
     * @param  TradableUpsertEvent  $event
     * @return void
     */
    public function handle(TradableUpsertEvent $event)
    {
        Log::info('product #' . $event->tradable->id . ' up-serted');
    }
}
