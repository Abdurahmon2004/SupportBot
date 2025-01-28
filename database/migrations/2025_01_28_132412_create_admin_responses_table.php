<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAcceptedMessagesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accepted_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('admin_chat_id'); // Adminning Telegram ID
            $table->bigInteger('user_chat_id'); // Foydalanuvchining Telegram ID
            $table->timestamps(); // Qabul qilingan vaqt
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accepted_messages');
    }
}
