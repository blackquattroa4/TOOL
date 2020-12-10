<?php

namespace App\Listeners;

use App\Events\TransactableUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class TransactableUpsertListener implements ShouldQueue
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
     * @param  TransactableUpsertEvent  $event
     * @return void
     */
    public function handle(TransactableUpsertEvent $event)
    {
        Log::info('transactable #' . $event->transactable->id . ' up-serted');
    }
}
