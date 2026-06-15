<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendSuperviseurCredentials extends Command
{
    protected $signature = 'send:superviseur-credentials';
    protected $description = 'Envoyer les identifiants à un superviseur manuellement';

    public function handle()
    {
        $this->info('=== Envoi des identifiants du superviseur ===');

        // Demander l'email
        $email = $this->ask('Email du superviseur:');

        // Vérifier que l'email existe dans les users
        $superviseur = User::where('email', $email)->first();
        
        if (!$superviseur) {
            $this->error("❌ Aucun superviseur trouvé avec l'email: $email");
            return;
        }

        // Demander le mot de passe
        $password = $this->ask('Mot de passe à envoyer:');

        if (empty($password)) {
            $this->error('❌ Le mot de passe ne peut pas être vide');
            return;
        }

        // Confirmer avant d'envoyer
        $this->line('');
        $this->info('📧 Email du superviseur: ' . $superviseur->email);
        $this->info('👤 Nom: ' . $superviseur->nom_user);
        $this->line('');

        if (!$this->confirm('Envoyer l\'email avec ces identifiants ?')) {
            $this->warn('❌ Opération annulée');
            return;
        }

        try {
            // Envoyer l'email
            Mail::send(new \App\Mail\SuperviseurCredentialsMail($superviseur, $password));
            
            $this->info('✅ Email envoyé avec succès à ' . $superviseur->email);
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de l\'envoi: ' . $e->getMessage());
        }
    }
}
