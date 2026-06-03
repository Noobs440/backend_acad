<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class GenericNotification extends Notification
{
    use Queueable;

    protected $type;
    protected $projectId;
    protected $projectTitle;
    protected $message;

    public function __construct(string $type, int $projectId, ?string $projectTitle, string $message)
    {
        $this->type = $type;
        $this->projectId = $projectId;
        $this->projectTitle = $projectTitle;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'project_id' => $this->projectId,
            'project_title' => $this->projectTitle,
            'message' => $this->message,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
