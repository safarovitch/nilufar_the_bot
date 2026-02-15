<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'start';

    /**
     * @var string Command Description
     */
    protected string $description = 'Start Command to get you started';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $response = $this->getUpdate();
        $chatId = $response->getMessage()->getChat()->getId();

        $this->replyWithMessage([
            'text' => 'Hello! I am ready to rock!'
        ]);

        // This will trigger the commands list to be shown.
        // $this->triggerCommand('help');
    }
}
