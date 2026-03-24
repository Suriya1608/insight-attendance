<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LeaveRequestSubmittedNotification extends Notification
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
        $url  = url('/leave-requests/' . $lr->id);

        return (new MailMessage)
            ->subject('Leave Request Pending Your Approval — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($user->name . ' has submitted a ' . $lr->type_label . ' request that requires your approval.')
            ->line('**Date:** ' . $lr->request_date->format('d M Y'))
            ->line('**Type:** ' . $lr->type_label . ($lr->auto_lop ? ' *(auto-converted to LOP — CL exhausted)*' : ''))
            ->line('**Reason:** ' . $lr->reason)
            ->action('Review Request', $url)
            ->line('Please log in to approve or reject this request.');
    }
}
