<?php

namespace App\Notifications;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetApprovedNotification extends Notification
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

        return (new MailMessage)
            ->subject("Timesheet Fully Approved - {$ts->date->format('d M Y')}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your timesheet for {$ts->date->format('l, d M Y')} has been fully approved.")
            ->line('Total Logged Hours: ' . $ts->formatted_total_hours)
            ->action('View Timesheet', $this->url)
            ->line('Thank you for submitting your work report on time.');
    }
}
