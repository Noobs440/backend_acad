<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SuperviseurCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $superviseur;
    public $password;

    public function __construct(User $superviseur, $password)
    {
        $this->superviseur = $superviseur;
        $this->password = $password;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->to($this->superviseur->email)
                    ->subject('Vos identifiants de connexion - ' . config('app.name'))
                    ->view('emails.superviseur-credentials');
    }
}
