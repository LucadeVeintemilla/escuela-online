<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('docentes') && !Schema::hasColumn('docentes', 'user_id')) {
            Schema::table('docentes', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('docentes') && Schema::hasColumn('docentes', 'user_id')) {
            Schema::table('docentes', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
