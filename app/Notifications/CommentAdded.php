<?php

namespace App\Notifications;

use App\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CommentAdded extends Notification
{
    use Queueable;

    /**
     * @var Comment
     */
    private $comment;

    /**
     * Create a new notification instance.
     *
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $post     = $this->comment->post;
        $url      = url('/api/posts/'. $post->id);
        $typeOfPost = $post->author->id === $notifiable->id ? 'of yours.' : "you've previously commented on.";

        return (new MailMessage)
            ->subject('New Comment on : ' . $post->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->comment->user->name . ' recently commented on a post ' . $typeOfPost)
            ->line($this->comment->body)
            ->action('View Post', $url);
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
            //
        ];
    }
}
