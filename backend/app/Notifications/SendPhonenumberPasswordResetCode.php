<?php

namespace App\Notifications;

use HirbodKhatami\SmsPackage\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPhonenumberPasswordResetCode extends Notification
{
    use Queueable;

    private int $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['sms'];
    }

    public function toSms($notifiable)
    {
        return (new Sms)
            ->to(isset($notifiable->phonenumber) ? $notifiable->phonenumber : $notifiable->routes['phonenumber'])
            ->text(trans_choice(
                'auth.phonenumber_verification_text_message_sms',
                0,
                ['code' => strval($this->code)]
            ))
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
