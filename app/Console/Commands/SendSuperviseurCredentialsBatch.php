<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendSuperviseurCredentialsBatch extends Command
{
    protected $signature = 'send:superviseur-credentials-batch';
    protected $description = 'Envoyer les identifiants à plusieurs superviseurs depuis un fichier CSV';

    public function handle()
    {
        $this->info('=== Envoi en lot des identifiants des superviseurs ===');
        
        $filePath = base_path('superviseurs_credentials.csv');

        if (!file_exists($filePath)) {
            $this->error("❌ Fichier non trouvé: $filePath");
            return;
        }

        $file = fopen($filePath, 'r');
        $header = fgetcsv($file); // Lire l'en-tête

        $sent = 0;
        $failed = 0;
        $errors = [];

        $this->line('');
        $this->info('📧 Traitement des superviseurs...');
        $this->line('');

        while ($row = fgetcsv($file)) {
            if (count($row) < 2) continue;

            $email = trim($row[0]);
            $password = trim($row[1]);

            if (empty($email) || empty($password)) {
                continue;
            }

            // Vérifier que l'email existe
            $superviseur = User::where('email', $email)->first();
            
            if (!$superviseur) {
                $this->warn("⚠️  Superviseur introuvable: $email");
                $errors[] = "Superviseur introuvable: $email";
                $failed++;
                continue;
            }

            try {
                // Envoyer l'email
                Mail::send(new \App\Mail\SuperviseurCredentialsMail($superviseur, $password));
                
                $this->info("✅ Email envoyé à " . $superviseur->email);
                $sent++;
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour $email: " . $e->getMessage());
                $errors[] = "Erreur pour $email: " . $e->getMessage();
                $failed++;
            }
        }

        fclose($file);

        $this->line('');
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║         RÉSUMÉ DE L\'ENVOI              ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->info("✅ Emails envoyés : $sent");
        $this->error("❌ Emails échoués : $failed");
        $this->line('');

        if (!empty($errors)) {
            $this->warn('Erreurs rencontrées:');
            foreach ($errors as $error) {
                $this->line("  - $error");
            }
        }
    }
}
