<?php

namespace App\Notifications;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetSubmittedNotification extends Notification
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
            ->subject("Timesheet Submitted for Review - {$ts->date->format('d M Y')}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("{$name} has submitted a timesheet for {$ts->date->format('l, d M Y')} and it requires your review.")
            ->line('Total Logged Hours: ' . $ts->formatted_total_hours)
            ->line('Entries: ' . $ts->entries->count() . ' work block(s)')
            ->action('Review Timesheet', $this->url)
            ->line('Please sign in to approve or reject this timesheet.');
    }
}
