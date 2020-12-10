<?php

namespace App\Listeners;

use App\Events\InventoryUpdateEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class InventoryUpdateListener implements ShouldQueue
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
     * @param  InventoryUpdateEvent  $event
     * @return void
     */
    public function handle(InventoryUpdateEvent $event)
    {
        Log::info('Inventory updated with transaction #' . $event->transaction->id );
    }
}
