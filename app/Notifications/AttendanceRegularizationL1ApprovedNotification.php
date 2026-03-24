<?php

namespace App\Notifications;

use App\Models\AttendanceRegularization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceRegularizationL1ApprovedNotification extends Notification
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
            ->subject('Attendance Regularization Awaiting Final Approval - ' . $r->date->format('d M Y'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($r->user->name . "'s attendance regularization was approved by L1 and needs your final review.")
            ->line('Date: ' . $r->date->format('l, d M Y'))
            ->line('Requested Times: ' . $r->requested_times_label)
            ->action('Review Request', $this->url);
    }
}
