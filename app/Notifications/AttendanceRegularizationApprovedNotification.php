<?php

namespace App\Notifications;

use App\Models\AttendanceRegularization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceRegularizationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public AttendanceRegularization $regularization, public string $url) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $r = $this->regularization;

        return (new MailMessage)
            ->subject('Attendance Regularization Approved - ' . $r->date->format('d M Y'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your attendance regularization request has been fully approved.')
            ->line('Date: ' . $r->date->format('l, d M Y'))
            ->line('Requested Times: ' . $r->requested_times_label)
            ->action('View Request', $this->url);
    }
}
