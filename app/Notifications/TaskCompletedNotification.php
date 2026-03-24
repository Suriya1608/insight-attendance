<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(public Task $task) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $task = $this->task;
        $url  = url('/manager/tasks/' . $task->id);

        return (new MailMessage)
            ->subject('Task Completed — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($task->assignee->name . ' has marked the following task as completed.')
            ->line('**Task:** ' . $task->title)
            ->line('**Priority:** ' . $task->priorityLabel())
            ->line('**Due Date:** ' . $task->due_date->format('d M Y'))
            ->action('View Task', $url)
            ->line('Log in to review the completed task.');
    }
}
