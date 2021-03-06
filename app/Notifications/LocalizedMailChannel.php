<?php

namespace App\Notifications;


use App\User;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

/**
 * MailChannel that sets the locale of the e-mail to that of the recipient
 */
class LocalizedMailChannel extends MailChannel
{
    /**
     * @inheritDoc
     *
     * @param User|Notifiable $notifiable
     */
    public function send($notifiable, Notification $notification)
    {
        $originalLocale = \App::getLocale();
        $locale         = isset($notifiable->locale) ? $notifiable->locale
            : null;

        $differentLocale = $locale !== null && $locale !== $originalLocale;

        if ($differentLocale) {
            \App::setLocale($locale);
        }

        parent::send($notifiable, $notification);

        if ($differentLocale) {
            \App::setLocale($originalLocale);
        }
    }

}