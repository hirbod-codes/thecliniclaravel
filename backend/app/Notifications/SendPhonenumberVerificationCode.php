<?php

namespace App\Notifications;

use HirbodKhatami\SmsPackage\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPhonenumberVerificationCode extends Notification implements ShouldQueue
{
    use Queueable;

    private int $code;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $code)
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
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
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
