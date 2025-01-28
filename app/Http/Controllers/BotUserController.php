<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Laravel\Facades\Telegram;

class BotUserController extends Controller
{
    public function webhook(Request $request)
    {
        $update = Telegram::getWebhookUpdates();

        $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'] ?? null;
        $text = $update['message']['text'] ?? null;
        $messageId = $update['message']['message_id'] ?? null;
        // Foydalanuvchining birinchi xabari (private chat)
        if (isset($update['message']['chat']['type']) && $update['message']['chat']['type'] === 'private') {
            $admin = DB::table('user_messages')->where('admin_chat_id', $chatId)->get();
            if($admin){
                $userChatId = $admin->user_chat_id;
                Telegram::sendMessage([
                    'chat_id'=>$userChatId,
                    'text'=> $text,
                ]);
                return;
            }
            if ($text === '/start') {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Assalomu alaykum\nBizning qo'llab-quvvatlash botimizga xush kelibsiz! Har qanday savolingizni yozib qoldiring. Jamoamiz tez orada sizga javob beradi. ".$chatId,
                    'parse_mode' => 'HTML',
                ]);
                return;
            }
            DB::table('user_messages')->insert([
                'chat_id' => $chatId,
                'message' => $text,
                'message_id' => $messageId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Guruhga xabarni forward qilish
            Telegram::sendMessage([
                'chat_id' => -4796380741, // Guruh ID
                'text' => "Yangi murojaat kelib tushdi:\n\n<b>Foydalanuvchi:</b> $chatId\n<b>Xabar:</b> $text",
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => 'Qabul qilish', 'callback_data' => "accept:$chatId"],
                        ],
                    ],
                ]),
            ]);
        }

        // "Qabul qilish" tugmasi bosilganda
        if (isset($update['callback_query'])) {
            $callbackQuery = $update['callback_query'];
            $data = $callbackQuery['data'];
            $adminChatId = $callbackQuery['from']['id'];

            if (str_starts_with($data, 'accept:')) {
                $userChatId = explode(':', $data)[1];

                // Foydalanuvchining barcha xabarlarini olish
                $userMessages = DB::table('user_messages')
                    ->where('chat_id', $userChatId)
                    ->orderBy('created_at')
                    ->get();

                $messagesText = "Foydalanuvchi ($userChatId) yozgan xabarlar:\n";
                foreach ($userMessages as $msg) {
                    $messagesText .= "- {$msg->message}\n";
                }

                // Adminga foydalanuvchi xabarlarini yuborish
                Telegram::sendMessage([
                    'chat_id' => $adminChatId,
                    'text' => $messagesText,
                ]);
                DB::table('accepted_messages')->insert([
                    'admin_chat_id' => $adminChatId,
                    'user_chat_id' => $userChatId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                // Guruhga qabul qilindi deb xabar qilish
                Telegram::editMessageText([
                    'chat_id' => -4796380741, // Guruh ID
                    'message_id' => $callbackQuery['message']['message_id'],
                    'text' => "Murojaat qabul qilindi va adminga yo'naltirildi.",
                ]);
            }
        }
    }
}
