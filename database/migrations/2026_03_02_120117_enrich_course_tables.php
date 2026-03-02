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
        Schema::table('course_modules', function (Blueprint $table) {
            $table->string('description')->nullable()->after('title');
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->string('description')->nullable()->after('title');
            $table->boolean('is_published')->default(true)->after('sequence');
            $table->boolean('allow_comments')->default(false)->after('is_published');
            $table->boolean('is_preview')->default(false)->after('allow_comments');
        });

        Schema::table('course_materials', function (Blueprint $table) {
            $table->string('title')->nullable()->after('course_lesson_id');
            $table->string('url')->nullable()->after('type');
            $table->string('thumbnail_url')->nullable()->after('url');
            $table->boolean('show_download_link')->default(false)->after('thumbnail_url');
            $table->string('subtitle_source')->nullable()->after('show_download_link'); // auto-generate or upload
            $table->string('subtitles_url')->nullable()->after('subtitle_source');
            $table->string('audio_language')->nullable()->after('subtitles_url');
            $table->json('settings')->nullable()->after('audio_language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_materials', function (Blueprint $table) {
            $table->dropColumn(['title', 'url', 'thumbnail_url', 'show_download_link', 'subtitle_source', 'subtitles_url', 'audio_language', 'settings']);
        });

        Schema::table('course_lessons', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_published', 'allow_comments', 'is_preview']);
        });

        Schema::table('course_modules', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
