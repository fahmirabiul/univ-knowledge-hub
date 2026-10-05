<?php

declare(strict_types=1);

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
        Schema::create('publication_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name');
            $table->string('author_email')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('author_role', 50)->default('pendamping')->index();
            $table->unsignedSmallInteger('author_order')->default(1);
            $table->boolean('is_corresponding')->default(false);
            $table->boolean('is_external')->default(false)->index();
            $table->timestamps();

            $table->index(['publication_id', 'author_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publication_authors');
    }
};
