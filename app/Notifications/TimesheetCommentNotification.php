<?php

namespace App\Notifications;

use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetCommentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Timesheet $timesheet,
        public User $author,
        public string $comment,
        public string $url
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $snippet = mb_strlen($this->comment) > 160
            ? mb_substr($this->comment, 0, 160) . '...'
            : $this->comment;

        return (new MailMessage)
            ->subject("New Comment on Timesheet - {$this->timesheet->date->format('d M Y')}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("{$this->author->name} added a comment on the timesheet for {$this->timesheet->date->format('l, d M Y')}.")
            ->line('Comment: "' . $snippet . '"')
            ->action('View Timesheet', $this->url);
    }
}
