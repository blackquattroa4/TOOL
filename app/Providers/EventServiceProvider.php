<?php

namespace App\Providers;

use App\Events\AccountUpsertEvent;
use App\Events\DocumentUpsertEvent;
use App\Events\EntityUpsertEvent;
use App\Events\ExpenseUpsertEvent;
use App\Events\InventoryUpdateEvent;
use App\Events\LoanUpsertEvent;
use App\Events\PurchaseUpsertEvent;
use App\Events\RmaUpsertEvent;
use App\Events\SalesUpsertEvent;
use App\Events\TradableUpsertEvent;
use App\Events\TransactableUpsertEvent;
use App\Events\WarehouseUpsertEvent;

use App\Listeners\AccountUpsertListener;
use App\Listeners\DocumentUpsertListener;
use App\Listeners\EntityUpsertListener;
use App\Listeners\ExpenseUpsertListener;
use App\Listeners\InventoryUpdateListener;
use App\Listeners\LoanUpsertListener;
use App\Listeners\PurchaseUpsertListener;
use App\Listeners\RmaUpsertListener;
use App\Listeners\SalesUpsertListener;
use App\Listeners\TradableUpsertListener;
use App\Listeners\TransactableUpsertListener;
use App\Listeners\WarehouseUpsertListener;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\TradableTransaction;
use App\Observers\TradableTransactionObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        AccountUpsertEvent::class => [
            AccountUpsertListener::class,
        ],
        DocumentUpsertEvent::class => [
            DocumentUpsertListener::class,
        ],
        EntityUpsertEvent::class => [
            EntityUpsertListener::class,
        ],
        ExpenseUpsertEvent::class => [
            ExpenseUpsertListener::class,
        ],
        InventoryUpdateEvent::class => [
            InventoryUpdateListener::class,
        ],
        LoanUpsertEvent::class => [
            LoanUpsertListener::class,
        ],
        PurchaseUpsertEvent::class => [
            PurchaseUpsertListener::class,
        ],
        RmaUpsertEvent::class => [
            RmaUpsertListener::class,
        ],
        SalesUpsertEvent::class => [
            SalesUpsertListener::class,
        ],
        TradableUpsertEvent::class => [
            TradableUpsertListener::class,
        ],
        TransactableUpsertEvent::class => [
            TransactableUpsertListener::class,
        ],
        WarehouseUpsertEvent::class => [
            WarehouseUpsertListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // make TradableTransactionObserver to observe respective object
        TradableTransaction::observe(new TradableTransactionObserver());
    }
}
