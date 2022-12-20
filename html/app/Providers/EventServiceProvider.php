<?php

namespace App\Providers;

use App\Events\OrderReceiptEvent;
use App\Events\SohibNotificationFromSubConsole;
use App\Events\SubConsoleOrder;
use App\Events\SubConsoleOrderCanceled;
use App\Events\SubConsoleOrderDeleted;
use App\Listeners\SendOrderReceiptMail;
use App\Listeners\SendSohibNotification;
use App\Listeners\SendSubConsoleCancelNotification;
use App\Listeners\SendSubConsoleDeletedNotification;
use App\Listeners\SendSubConsoleNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

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
        SubConsoleOrder::class => [
            SendSubConsoleNotification::class,
        ],
        SubConsoleOrderCanceled::class => [
            SendSubConsoleCancelNotification::class,
        ],
        SohibNotificationFromSubConsole::class => [
            SendSohibNotification::class,
        ],
        OrderReceiptEvent::class => [
            SendOrderReceiptMail::class,
        ],
        SubConsoleOrderDeleted::class => [
            SendSubConsoleDeletedNotification::class,
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

        //
    }
}
