<?php

namespace App\Providers;

use App\Listeners\SendSmsVerificationNotification;
use App\Notifications\QueueHasLongWaitTime;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Queue\Events\QueueBusy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        try {
            Event::listen(function (QueueBusy $event) {
                Notification::route('mail', 'hirbod.khatami@gmail.com')
                    ->route('sms', '09380978577')
                    ->notify(new QueueHasLongWaitTime(
                        $event->connection,
                        $event->queue,
                        $event->size
                    ));
            });
        } catch (\Throwable $th) {
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
