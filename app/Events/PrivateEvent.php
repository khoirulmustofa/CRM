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

    public $userTo;
    public $userMe;
    public $messageContent;

    /**
     * Create a new event instance.
     */
    public function __construct(User $userTo, User $userMe, $messageContent)
    {
        $this->userTo = $userTo;
        $this->userMe = $userMe;
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
            new PrivateChannel('App.Models.User.' . $this->userTo->id),
        ];
    }

    public function broadcastWith(): array
    {
        return array(
            'to' => $this->userTo->name,
            'from' => $this->userMe->name,
            'message' => $this->messageContent,
        );
    }

    public function broadcastAs(): string
    {
        return 'user.notification';
    }
}
