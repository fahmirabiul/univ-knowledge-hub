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
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submitter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('doi')->nullable()->unique()->index();
            $table->string('title', 500)->index();
            $table->longText('abstract');
            $table->string('journal_name')->index();
            $table->unsignedSmallInteger('publication_year')->index();
            $table->string('volume', 50)->nullable();
            $table->string('issue', 50)->nullable();
            $table->string('pages', 50)->nullable();
            $table->string('status', 30)->default('pending')->index();
            $table->string('file_path', 500)->nullable();
            $table->string('original_filename', 255)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->json('metadata_api')->nullable();
            $table->unsignedInteger('citation_count')->default(0);
            $table->boolean('is_public')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
