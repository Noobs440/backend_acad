<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $project;
    protected $senderName;

    public function __construct($project, string $senderName = '')
    {
        $this->project = $project;
        $this->senderName = $senderName ?: optional($project->user)->nom_user ?: optional($project->user)->name ?: 'un utilisateur';
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Le projet "' . $this->project->titre_projet . '" a été soumis pour validation par ' . $this->senderName . '.')
                    ->action('Voir le projet', url('/projects/' . $this->project->id))
                    ->line('Merci de vérifier le projet.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'project_submitted',
            'project_id' => $this->project->id,
            'projet_id' => $this->project->id,
            'project_title' => $this->project->titre_projet,
            'submitted_by' => $this->senderName,
            'status' => 'Pending',
            'message' => 'Le projet "' . $this->project->titre_projet . '" a été soumis pour validation par ' . $this->senderName . '.'
        ];
    }
}

