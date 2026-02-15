<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;
use Telegram\Bot\Keyboard\Keyboard;

class LoginCommand extends Command
{
    protected string $name = 'login';
    protected string $description = 'Login to sync your music history';

    public function handle()
    {
        $chatId = $this->getUpdate()->getMessage()->getChat()->getId();

        // Generate Login URLs
        // We append telegram_id to the URL to link the account.
        // We assume the app is hosted at APP_URL.
        $appUrl = config('app.url');
        $googleUrl = "$appUrl/auth/google?telegram_id=$chatId";
        $yandexUrl = "$appUrl/auth/yandex?telegram_id=$chatId";

        $keyboard = Keyboard::make()->inline()
            ->row([
                Keyboard::inlineButton(['text' => 'Login with Google', 'url' => $googleUrl]),
            ])
            ->row([
                Keyboard::inlineButton(['text' => 'Login with Yandex', 'url' => $yandexUrl]),
            ]);

        $this->replyWithMessage([
            'text' => 'Please choose a service to login and sync your history:',
            'reply_markup' => $keyboard
        ]);
    }
}
