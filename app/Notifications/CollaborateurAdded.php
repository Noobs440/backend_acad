<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CollaborateurAdded extends Notification
{
    use Queueable;

    protected $nom;

    public function __construct($nom)
    {
        $this->nom = $nom;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Vous avez été ajouté comme collaborateur')
                    ->greeting('Bonjour ' . $this->nom . ',')
                    ->line('Vous avez été ajouté comme collaborateur à un projet sur notre plateforme.')
                    ->line('Merci de votre participation.')
                    ->salutation('Cordialement, L’équipe');
    }
}
