<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->index(); // Foydalanuvchi Telegram chat ID
            $table->text('message');              // Foydalanuvchi xabari
            $table->bigInteger('message_id');     // Telegram xabar ID
            $table->timestamps();                 // created_at va updated_at ustunlar
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_messages');
    }
}
