<?php

namespace App\Events;

use App\Models\Likes;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LikeEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $like;
    protected $user;
    protected $tweet;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Likes $like, User $user, Tweet $tweet)
    {
        $this->tweet = $tweet;
        $this->user = $user;
        $this->like = $like;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('tweet.'.$this->tweet->id);
    }
    public function broadcastAs()
    {
        return 'NewLike';
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->user->name,
            'userId' => $this->user->id,
        ];
    }
}
