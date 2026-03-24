<?php

namespace App\Notifications;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetL1ApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Timesheet $timesheet,
        public string $url
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $ts = $this->timesheet;
        $name = $ts->user->name;

        return (new MailMessage)
            ->subject('Timesheet Approved by L1 - Awaiting Your Review')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("{$name}'s timesheet for {$ts->date->format('l, d M Y')} has been approved by the Level 1 manager and now requires your final review.")
            ->line('Total Logged Hours: ' . $ts->formatted_total_hours)
            ->action('Review Timesheet', $this->url)
            ->line('Please sign in to give final approval or rejection.');
    }
}
