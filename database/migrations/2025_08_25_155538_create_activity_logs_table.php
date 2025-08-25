<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Qui a fait l'action (souvent User). On met nullable pour gérer les jobs/CLI.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Quelle ressource (modèle) est concernée ?
            // "subject" = entité cible (ex: User, Projet, Document...)
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);

            // Quel événement ?
            // created | updated | deleted | restored | force_deleted | etc.
            $table->string('event');

            // Un petit résumé optionnel
            $table->string('description')->nullable();

            // Diff des données
            $table->json('old_values')->nullable(); // valeurs avant (pour update/delete)
            $table->json('new_values')->nullable(); // valeurs après (pour create/update/restore)

            // Contexte requête (utile pour l’audit)
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps(); // created_at = horodatage de l’événement
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
