<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('tbl_projets', function (Blueprint $table) {
            if (!Schema::hasColumn('tbl_projets', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('status');
            }
        });
    }
    public function down() {
        Schema::table('tbl_projets', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_projets', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};
