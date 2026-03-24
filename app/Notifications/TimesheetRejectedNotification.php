<?php

namespace App\Notifications;

use App\Models\Timesheet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TimesheetRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Timesheet $timesheet,
        public string $url,
        public string $rejectedBy,
        public string $remarks
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $ts = $this->timesheet;

        return (new MailMessage)
            ->subject("Timesheet Rejected - {$ts->date->format('d M Y')}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your timesheet for {$ts->date->format('l, d M Y')} has been rejected by {$this->rejectedBy}.")
            ->line('Remarks: ' . ($this->remarks !== '' ? $this->remarks : 'No remarks provided.'))
            ->action('View and Revise Timesheet', $this->url)
            ->line('Please update your timesheet and submit it again for approval.');
    }
}
