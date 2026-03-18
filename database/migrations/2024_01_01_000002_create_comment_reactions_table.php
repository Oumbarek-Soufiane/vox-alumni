<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comment_reactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_id');
            $table->string('emoji', 20);         // 'heart' | 'fire' | 'wow' | 'clap' | 'laugh'
            $table->string('user_token', 64);    // anonymous fingerprint from cookie
            $table->timestamps();

            // One reaction per emoji per user per comment
            $table->unique(['comment_id', 'emoji', 'user_token'], 'unique_comment_reaction');
            $table->index('comment_id');

            $table->foreign('comment_id')
                  ->references('id')
                  ->on('comments')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
