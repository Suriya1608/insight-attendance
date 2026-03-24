<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRequestRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $lr      = $this->leaveRequest;
        $remarks = $lr->l2_remarks ?? $lr->l1_remarks ?? 'No remarks provided.';
        $url     = url('/leave-requests/' . $lr->id);

        return (new MailMessage)
            ->subject('Your ' . $lr->type_label . ' Request Has Been Rejected — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your ' . $lr->type_label . ' request has been rejected.')
            ->line('**Date:** ' . $lr->request_date->format('d M Y'))
            ->line('**Type:** ' . $lr->type_label)
            ->line('**Rejection Reason:** ' . $remarks)
            ->action('View Request', $url)
            ->line('Please contact your manager for further clarification.');
    }
}
