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
        Schema::create('logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->comment('Log benzersiz kimliği');
            $table->string('ip_address', 45)->comment('İstek yapan IP adresi'); // IPv6 için 45 karakter
            $table->string('method', 10)->comment('HTTP metodu (GET, POST, vb.)');
            $table->string('url', 500)->comment('İstek yapılan URL');
            $table->text('user_agent')->nullable()->comment('Kullanıcı tarayıcı bilgisi');
            $table->json('headers')->nullable()->comment('İstek başlıkları');
            $table->json('request_data')->nullable()->comment('İstek verileri');
            $table->integer('response_status')->nullable()->comment('Yanıt durum kodu');
            $table->integer('response_time')->nullable()->comment('Yanıt süresi (ms)');
            $table->string('bearer_token_used', 50)->nullable()->comment('Kullanılan bearer token (güvenlik için hash)');
            $table->boolean('is_authenticated')->default(false)->comment('Kimlik doğrulama durumu');
            $table->timestamps();

            // Performans için indexler
            $table->index('ip_address', 'logs_ip_address_index');
            $table->index('method', 'logs_method_index');
            $table->index('created_at', 'logs_created_at_index');
            $table->index(['ip_address', 'created_at'], 'logs_ip_created_index');
            $table->index('is_authenticated', 'logs_authenticated_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
