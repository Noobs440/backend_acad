<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_projets', function (Blueprint $table) {
            // Si la colonne est ENUM, on la modifie pour ajouter 'Not Submit'.
            // Sinon, on la convertit en VARCHAR(50) pour plus de flexibilité.
            $table->string('status', 50)->default('Not Submit')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_projets', function (Blueprint $table) {
            // Remettre l'ancien type si besoin (à adapter selon l'ancien type)
            // Exemple : $table->enum('status', ['En attente', 'Validé', 'Rejeté'])->default('En attente')->change();
        });
    }
};
