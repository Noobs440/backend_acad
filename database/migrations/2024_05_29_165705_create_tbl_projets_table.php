<?php

use App\Models\TblCategorie;
use App\Models\TblNiveau;
use App\Models\User;
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
        Schema::create('tbl_projets', function (Blueprint $table) {
            $table->id();
            $table->string('titre_projet')->unique();
            $table->text('descript_projet');
            $table->unsignedBigInteger('tbl_niveau_id');
            $table->integer('views')->default(0);
            $table->string('image');
            $table->enum('status', ['Not Submitted','Pending', 'Approved', 'Rejected', 'Submitted'])->default('Not Submitted');
            $table->enum('type', ['Projet', 'Memoire', 'Article'])->default('Projet');
            $table->boolean('soumis')->default(false);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreign('tbl_niveau_id')->references('id')->on('tbl_niveaux')->onDelete('cascade');
            $table->foreignIdFor(User::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(TblCategorie::class)->constrained()->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_projets');
    }
};
