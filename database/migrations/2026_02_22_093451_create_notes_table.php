<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('notebook_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->default('Ohne Titel');
            $table->longText('content')->nullable();       // Tiptap HTML
            $table->json('content_json')->nullable();      // Tiptap JSON (für Suche)
            $table->boolean('is_favorite')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->softDeletes();                         // Papierkorb
            $table->timestamps();

            $table->index(['user_id', 'deleted_at']);
            $table->fullText(['title', 'content'], 'notes_fulltext');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
