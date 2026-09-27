<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', ['token' => $this->token], false)
            .'?email='.urlencode($notifiable->getEmailForPasswordReset()));

        return (new MailMessage)
            ->subject('SHIRT STORE - Đặt lại mật khẩu')
            ->view('emails.auth.reset-password', [
                'name' => $notifiable->name,
                'url' => $url,
                'expires' => (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire'),
            ]);
    }
}
