<?php

namespace App\Notifications;

use HirbodKhatami\SmsPackage\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QueueHasLongWaitTime extends Notification
{
    use Queueable;

    private string $connection;

    private string $queue;

    private int $size;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $connection, string $queue, int $size)
    {
        $this->connection =  $connection;
        $this->queue =  $queue;
        $this->size =  $size;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'sms'];
    }

    public function toSms($notifiable)
    {
        return (new Sms)
            ->to(isset($notifiable->phonenumber) ? $notifiable->phonenumber : $notifiable->routes['phonenumber'])
            ->text("The " . $this->queue . " queue of " . $this->connection . "connection is busy.\nThe queue's size is: " . strval($this->size))
            //
        ;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line("The " . $this->queue . " queue of " . $this->connection . "connection is busy.\nThe queue's size is: " . strval($this->size))
            ->action("Home page", url("/"))
            //
        ;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
