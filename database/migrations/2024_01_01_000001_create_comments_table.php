<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('artwork_id', 120);              // slug e.g. "bilmawn"
            $table->unsignedBigInteger('parent_id')->nullable(); // null = top-level
            $table->string('author_name', 80);
            $table->text('body');
            $table->timestamps();

            $table->index('artwork_id');
            $table->index('parent_id');

            // A reply cannot reply to another reply (1 level deep)
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('comments')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
