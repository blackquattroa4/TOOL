<?php

namespace App\Listeners;

use App\Events\WarehouseUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class WarehouseUpsertListener implements ShouldQueue
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
     * @param  WarehouseUpsertEvent  $event
     * @return void
     */
    public function handle(WarehouseUpsertEvent $event)
    {
        Log::info('warehouse #' . $event->warehouse->id . ' up-serted');
    }
}
