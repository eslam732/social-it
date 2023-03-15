<?php

namespace App\Notifications;

use App\Models\Likes;
use App\Models\Tweet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Like extends Notification
{
    use Queueable;

    public $user;
    public $tweet;
    public $like;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user,Tweet $tweet,Likes $like)
    {
        $this->user=$user;
        $this->tweet=$tweet;
        $this->like=$like;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

   

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            "type" => 'Like',
            "like_id" => $this->like->id,
            "like_time" => $this->like->updated_at,
            "info" => $this->user->name . " liked your tweet " . $this->tweet->content,
            "user_id" => $this->user->id,
            "user_name" => $this->user->name,
            "user_picture" => $this->user->picture,
            "tweet_id" => $this->tweet->id,
            
        ];
    }
}
