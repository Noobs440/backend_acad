<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class CollaborateurAdded extends Notification
{
    use Queueable;

    protected $projet;
    protected $adder;

    /**
     * @param $projet TblProjet
     * @param $adder User
     */
    public function __construct($projet, $adder)
    {
        $this->projet = $projet;
        $this->adder = $adder;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Vous avez été ajouté comme collaborateur')
            ->greeting('Bonjour,')
            ->line('Vous avez été ajouté comme collaborateur au projet "' . $this->projet->titre_projet . '" sur notre plateforme.')
            ->line('Ajouté par : ' . $this->adder->nom_user . ' (' . $this->adder->email . ')')
            ->salutation('Cordialement, L’équipe');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'collaborateur_added',
            'project_id' => $this->projet->id,
            'project_title' => $this->projet->titre_projet,
            'adder' => [
                'id' => $this->adder->id,
                'nom_user' => $this->adder->nom_user,
                'email' => $this->adder->email,
                'photo' => $this->adder->photo,
            ],
            'message' => 'Vous avez été ajouté comme collaborateur au projet "' . $this->projet->titre_projet . '" par ' . $this->adder->nom_user . '.',
        ];
    }
}
