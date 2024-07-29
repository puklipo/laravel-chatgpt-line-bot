<?php

declare(strict_types=1);

namespace App\Listeners\Line;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Arr;
use LINE\Clients\MessagingApi\ApiException;
use LINE\Webhook\Model\MessageEvent;
use LINE\Webhook\Model\TextMessageContent;
use OpenAI\Laravel\Facades\OpenAI;
use Revolution\Line\Facades\Bot;

class MessageListener
{
    protected string $token;

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
     * @param  MessageEvent  $event
     * @return void
     *
     * @throws ApiException
     */
    public function handle(MessageEvent $event): void
    {
        $message = $event->getMessage();
        $this->token = $event->getReplyToken();

        match (get_class($message)) {
            TextMessageContent::class => $this->text($message),
        };
    }

    /**
     * @throws ApiException
     */
    protected function text(TextMessageContent $message): void
    {
        $text = $message->getText();

        $chat = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'temperature' => 0.7,
            'max_tokens' => 500,
            'messages' => [
                ['role' => 'user', 'content' => $text],
            ],
        ]);

        $content = Arr::get($chat, 'choices.0.message.content');

        if (blank($content)) {
            return;
        }

        Bot::reply($this->token)->text($content);
    }
}
