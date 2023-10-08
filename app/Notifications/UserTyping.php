<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class UserTyping extends Notification implements ShouldQueue
{
    use Queueable;

    public $chat_id;
    public $is_typing;

    /**
     * Create a new notification instance.
     */
    public function __construct($chat_id, $is_typing)
    {
        $this->chat_id = $chat_id;
        $this->is_typing = $is_typing;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast($notification): BroadcastMessage
    {
        return new BroadcastMessage(['chat_id' => $this->chat_id, 'is_typing' => $this->is_typing]);
    }
}
