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
        Schema::create('played_tracks', function (Blueprint $table) {
            $table->id();
            // Assuming users table exists and uses bigIncrements/id
            $table->unsignedBigInteger('user_id')->nullable();
            // If we want to link strictly to a registered user, use foreign key, 
            // but for a bot, user_id might be the telegram user id or a local user id.
            // For now, let's assume valid local user id if they are registered via web/bot.
            // Or just store telegram_user_id if we don't have a users table mapping.
            // Let's stick to nullable user_id for now, or just index it.

            $table->string('track_source_id')->index(); // e.g. youtube video id
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('genre')->nullable();
            $table->timestamp('played_at')->useCurrent();
            $table->timestamps();

            // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('played_tracks');
    }
};
