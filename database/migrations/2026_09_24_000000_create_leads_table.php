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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->string('email', 180)->index();
            $table->string('telefone', 30);
            $table->string('telefone_e164', 20)->index();
            $table->string('como_conheceu', 30)->nullable();
            $table->text('mensagem')->nullable();
            $table->string('status', 30)->default('novo')->index();

            // LGPD compliance
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_version', 20)->nullable();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->string('user_agent', 255)->nullable();
            $table->string('referer', 255)->nullable();

            // Gestão interna & retenção
            $table->string('assigned_to', 120)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('anonymized_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
