<?php

namespace App\Listeners;

use App\Events\AccountUpsertEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

// implements 'ShouldQueue' interface & use 'InteractsWithQUeue' trait to work with queue
class AccountUpsertListener implements ShouldQueue
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
     * @param  AccountUpsertEvent  $event
     * @return void
     */
    public function handle(AccountUpsertEvent $event)
    {
        Log::info('account #' . $event->account->id . ' up-serted');
    }
}
