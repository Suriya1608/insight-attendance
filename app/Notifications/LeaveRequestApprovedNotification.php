<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRequestApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $lr  = $this->leaveRequest;
        $url = url('/leave-requests/' . $lr->id);

        return (new MailMessage)
            ->subject('Your ' . $lr->type_label . ' Request Has Been Approved — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! Your ' . $lr->type_label . ' request has been fully approved.')
            ->line('**Date:** ' . $lr->request_date->format('d M Y'))
            ->line('**Type:** ' . $lr->type_label)
            ->line('**Reason:** ' . $lr->reason)
            ->action('View Request', $url)
            ->line('If you have any questions, please contact your manager.');
    }
}
