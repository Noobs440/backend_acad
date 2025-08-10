<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tbl_documents', function (Blueprint $table) {
        $table->string('public_id')->nullable()->after('lien_doc');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_documents', function (Blueprint $table) {
            //
        });
    }
};
