<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRequestL1ApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $lr   = $this->leaveRequest;
        $user = $lr->user;
        $l1   = $lr->l1Manager;
        $url  = url('/leave-requests/' . $lr->id);

        return (new MailMessage)
            ->subject('Leave Request Awaiting Your Final Approval — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($user->name . '\'s ' . $lr->type_label . ' request has been approved by ' . ($l1?->name ?? 'L1 Manager') . ' and now requires your final approval.')
            ->line('**Date:** ' . $lr->request_date->format('d M Y'))
            ->line('**Type:** ' . $lr->type_label)
            ->line('**Reason:** ' . $lr->reason)
            ->action('Review Request', $url)
            ->line('Please log in to give your final approval or rejection.');
    }
}
