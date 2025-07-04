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
        Schema::table('user_templates', function (Blueprint $table) {
            $table->string('cover_path')->nullable()->after('preview_image');
            $table->string('cover_type')->nullable()->after('cover_path'); // 'image' или 'video'
            $table->string('cover_thumbnail')->nullable()->after('cover_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_templates', function (Blueprint $table) {
            $table->dropColumn('cover_path');
            $table->dropColumn('cover_type');
            $table->dropColumn('cover_thumbnail');
        });
    }
};
