<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('facebook_url')->nullable()->after('telephone');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->string('twitter_url')->nullable()->after('tiktok_url');
            $table->string('youtube_url')->nullable()->after('twitter_url');
            $table->string('linkedin_url')->nullable()->after('youtube_url');
            $table->string('website_url')->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_url', 'instagram_url', 'tiktok_url',
                'twitter_url', 'youtube_url', 'linkedin_url', 'website_url',
            ]);
        });
    }
};
