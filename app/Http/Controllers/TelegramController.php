<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function webhook(Request $request)
    {
        $update = Telegram::commandsHandler(true);

        // Handle Callback Query (Inline Button Clicks)
        if ($update->has('callback_query')) {
            $callbackQuery = $update->getCallbackQuery();
            $data = $callbackQuery->getData();
            $chatId = $callbackQuery->getMessage()->getChat()->getId();

            // Check if it's a download request (d:video_id)
            if (str_starts_with($data, 'd:')) {
                $videoId = substr($data, 2);

                Telegram::answerCallbackQuery([
                    'callback_query_id' => $callbackQuery->getId(),
                    'text' => 'Downloading...',
                ]);

                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Download started. Please wait...',
                ]);

                // Dispatch Job
                \App\Jobs\DownloadJob::dispatch($chatId, $videoId);
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
