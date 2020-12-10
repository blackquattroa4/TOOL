<?php

namespace App\Listeners;

use App\Events\PurchaseUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class PurchaseUpsertListener implements ShouldQueue
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
     * @param  PurchaseUpsertEvent  $event
     * @return void
     */
    public function handle(PurchaseUpsertEvent $event)
    {
        Log::info('purchase #' . $event->purchase->id . ' up-serted');
    }
}
