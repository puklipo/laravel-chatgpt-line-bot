<?php

declare(strict_types=1);

namespace App\Listeners\Message;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use OpenAI\Laravel\Facades\OpenAI;
use Revolution\Line\Facades\Bot;

class TextMessageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  TextMessage  $event
     * @return void
     */
    public function handle(TextMessage $event): void
    {
        $token = $event->getReplyToken();
        $text = $event->getText();

        $chat = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $text],
            ],
        ]);

        $content = Arr::get($chat, 'choices.0.message.content');

        $response = Bot::reply(token: $token)
                       ->text($content);

        if (! $response->isSucceeded()) {
            logger()->error(static::class.$response->getHTTPStatus(), $response->getJSONDecodedBody());
        }
    }
}
