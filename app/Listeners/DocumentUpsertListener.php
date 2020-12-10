<?php

namespace App\Listeners;

use App\Events\DocumentUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class DocumentUpsertListener implements ShouldQueue
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
     * @param  DocumentUpsertEvent  $event
     * @return void
     */
    public function handle(DocumentUpsertEvent $event)
    {
        Log::info('document #' . $event->document->id . ' up-serted');
    }
}
