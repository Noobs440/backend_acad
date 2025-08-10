<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('collaborateur_projet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tbl_collaborateur_id')->constrained('tbl_collaborateurs')->onDelete('cascade');
            $table->foreignId('tbl_projet_id')->constrained('tbl_projets')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['tbl_collaborateur_id', 'tbl_projet_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('collaborateur_projet');
    }
};
