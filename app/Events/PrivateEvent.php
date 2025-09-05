<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user; // The user receiving the message
    public $messageContent;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $messageContent)
    {
        $this->user = $user;
        $this->messageContent = $messageContent;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
       return [
            new PrivateChannel('App.Models.User.' . $this->user->id),
        ];
    }

    public function broadcastWith(): array
    {
        return array(
            'username' => __('app.welcome_message', ['username' => $this->user->name]),
            'message' => __('events.download_hawb_ready'),
        );
    }

    public function broadcastAs(): string
    {
        return 'user.notification';
    }
}
