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
        Schema::create('news', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('Haber idsi');
            $table->string("title", 255)->comment("Haber başlığı");
            $table->text("content")->comment("Haber içeriği");
            $table->string("slug")->unique()->comment("SEO Dostu URL");
            $table->string("image_path")->nullable()->comment("Haber görsel yolu");
            $table->enum("status", ["draft", "published","archived"])->default("draft")->comment("Haber Durumu");
            $table->timestamp('published_at')->nullable()->comment('Yayınlanma tarihi');
            $table->timestamps();

            // Performans içni indexler
            $table->index(['status', 'published_at'], 'news_status_published_index');
            $table->index('created_at', 'news_created_at_index');
            $table->index('title', 'news_title_index');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
