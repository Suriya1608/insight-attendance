<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Task $task, public ?string $taskUrl = null) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $task = $this->task;
        $url  = $this->taskUrl ?? url('/employee/tasks/' . $task->id);

        return (new MailMessage)
            ->subject('New Task Assigned — ' . config('app.name'))
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new task has been assigned to you by ' . $task->assigner->name . '.')
            ->line('**Task:** ' . $task->title)
            ->line('**Priority:** ' . $task->priorityLabel())
            ->line('**Start Date:** ' . $task->start_date->format('d M Y'))
            ->line('**Due Date:** ' . $task->due_date->format('d M Y'))
            ->action('View Task', $url)
            ->line('Please log in to view the task details and start working on it.');
    }
}
