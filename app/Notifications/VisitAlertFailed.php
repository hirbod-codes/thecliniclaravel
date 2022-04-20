<?php

namespace App\Notifications;

use App\Models\Auth\User as Authenticatable;
use HirbodKhatami\SmsPackage\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitAlertFailed extends Notification implements ShouldQueue
{
    use Queueable;

    private int $visitTimestamp;

    private mixed $failedNotifiable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $visitTimestamp, mixed $failedNotifiable)
    {
        $this->visitTimestamp = $visitTimestamp;
        $this->failedNotifiable = $failedNotifiable;
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
        if ($this->failedNotifiable instanceof  Authenticatable) {
            $failedNotifiable = 'user with username: \'' . $this->failedNotifiable->username . '\'';
        }

        return (new Sms)
            ->to($notifiable->phonenumber)
            ->text('System failed to notify ' . $failedNotifiable . ' for it\'s visit due at: ' . (new \DateTime)->setTimestamp($this->visitTimestamp)->format("Y-m-d H:i:s l P"));
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
            ->line('System failed to notify user with username: \'' . $notifiable->username . '\' for it\'s visit due at: ' . (new \DateTime)->setTimestamp($this->visitTimestamp)->format("Y-m-d H:i:s l P"));
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
