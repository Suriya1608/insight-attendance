<?php

namespace App\Notifications;

use App\Models\AttendanceRegularization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceRegularizationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public AttendanceRegularization $regularization,
        public string $url,
        public string $actionBy,
        public string $comment
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $r = $this->regularization;

        return (new MailMessage)
            ->subject('Attendance Regularization Rejected - ' . $r->date->format('d M Y'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your attendance regularization request was rejected by ' . $this->actionBy . '.')
            ->line('Date: ' . $r->date->format('l, d M Y'))
            ->line('Requested Times: ' . $r->requested_times_label)
            ->line('Comment: "' . $this->comment . '"')
            ->action('View Request', $this->url);
    }
}
