<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id'); // conversation liée à un projet
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('tbl_projets')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
}
