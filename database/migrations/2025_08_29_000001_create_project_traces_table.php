<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_traces', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projet_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type_modification');
            $table->json('infos_avant')->nullable();
            $table->json('infos_apres')->nullable();
            $table->timestamp('date_modification')->useCurrent();
            $table->foreign('projet_id')->references('id')->on('tbl_projets')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_traces');
    }
};
