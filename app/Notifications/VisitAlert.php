<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use HirbodKhatami\SmsPackage\Sms;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VisitAlert extends Notification implements ShouldQueue
{
    use Queueable;

    private int $visitTimestamp;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $visitTimestamp)
    {
        $this->visitTimestamp = $visitTimestamp;
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
            ->to($notifiable->phonenumber)
            ->text(trans_choice(
                'Visits/visits.visit_alert',
                0,
                ['visit_date' => (new \DateTime)->setTimestamp($this->visitTimestamp)->format("Y-m-d H:i:s l")]
            ));
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
            ->line(trans_choice(
                'Visits/visits.visit_reminder',
                0,
                ['visit_date' => (new \DateTime)->setTimestamp($this->visitTimestamp)->format("Y-m-d H:i:s l")]
            ))
            ->action('Home Page', url('/'))
            ->line('Thank you for choosing us.');
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
