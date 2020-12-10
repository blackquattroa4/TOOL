<?php

namespace App\Listeners;

use App\Events\EntityUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class EntityUpsertListener implements ShouldQueue
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
     * @param  EntityUpsertEvent  $event
     * @return void
     */
    public function handle(EntityUpsertEvent $event)
    {
        Log::info('entity #' . $event->entity->id . ' up-serted');
    }
}
