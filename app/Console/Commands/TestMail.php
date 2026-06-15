<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class TestMail extends Command
{
    protected $signature = 'mail:test {email}';
    protected $description = 'Tester l\'envoi d\'email';

    public function handle()
    {
        $email = $this->argument('email');

        $this->info('🧪 Test d\'envoi d\'email...');
        $this->line('Configuration SMTP:');
        $this->line('  MAIL_MAILER: ' . config('mail.default'));
        $this->line('  MAIL_HOST: ' . config('mail.mailers.smtp.host'));
        $this->line('  MAIL_PORT: ' . config('mail.mailers.smtp.port'));
        $this->line('  MAIL_ENCRYPTION: ' . config('mail.mailers.smtp.encryption'));
        $this->line('  MAIL_USERNAME: ' . config('mail.mailers.smtp.username'));
        $this->line('  MAIL_FROM_ADDRESS: ' . config('mail.from.address'));
        $this->line('');

        try {
            Mail::raw('Test d\'email - Si vous voyez ce message, le mail fonctionne !', function (Message $message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - CollabFacultyRise');
            });

            $this->info('✅ Email envoyé avec succès à ' . $email);
        } catch (\Exception $e) {
            $this->error('❌ Erreur: ' . $e->getMessage());
            $this->error('Détails: ' . $e->getTraceAsString());
        }
    }
}
