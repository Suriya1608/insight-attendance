<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task,
        public string $newStatus,
        public ?string $taskUrl = null
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $task      = $this->task;
        $url       = $this->taskUrl ?? url('/manager/tasks/' . $task->id);
        $statusLbl = ucwords(str_replace('_', ' ', $this->newStatus));
        $appName   = config('app.name');

        return (new MailMessage)
            ->subject("Task Status Updated — {$appName}")
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($task->assignee->name . ' has updated the status of the following task to **' . $statusLbl . '**.')
            ->line('**Task:** ' . $task->title)
            ->line('**Priority:** ' . $task->priorityLabel())
            ->line('**Due Date:** ' . $task->due_date->format('d M Y'))
            ->action('View Task', $url)
            ->line('Log in to review the task progress.');
    }
}
