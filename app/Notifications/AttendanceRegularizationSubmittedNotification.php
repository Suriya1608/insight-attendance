<?php

namespace App\Notifications;

use App\Models\AttendanceRegularization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceRegularizationSubmittedNotification extends Notification
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
            ->subject('Attendance Regularization Submitted - ' . $r->date->format('d M Y'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($r->user->name . ' submitted an attendance regularization request.')
            ->line('Date: ' . $r->date->format('l, d M Y'))
            ->line('Type: ' . $r->type_label)
            ->line('Requested Times: ' . $r->requested_times_label)
            ->action('Review Request', $this->url);
    }
}
