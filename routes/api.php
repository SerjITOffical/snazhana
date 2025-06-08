<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\GreetingController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\StickerController;

Route::post('/telegram/webhook', function (Request $request) {
    $greetingController = app(GreetingController::class);
    $keywordController = app(KeywordController::class);
    $stickerController = app(StickerController::class);

    $update = $greetingController->telegram->getWebhookUpdate();

    if (isset($update['message']['text'])) {
        $chat_id = $update['message']['chat']['id'];
        $message_text = $update['message']['text'];
        $message_id = $update['message']['message_id'];

        // Обработка приветствий
        if ($greetingController->handle($chat_id, $message_text, $message_id)) {
            return response()->json(['status' => 'greeted']);
        }

        // Обработка ключевых слов и ответ стикером
        if ($stickerController->handle($chat_id, $message_text, $message_id)) {
            return response()->json(['status' => 'sticker']);
        }

        // Обработка ключевых слов
        if ($keywordController->handle($chat_id, $message_text, $message_id)) {
            return response()->json(['status' => 'key_word']);
        }
    }

    return response()->json(['status' => 'ok']);
});

Route::get('/run-scheduler', function (Request $request) {
    $token = $request->query('token');

    if ($token !== env('PING_SECRET')) {
        abort(403, 'Access denied');
    }

    Artisan::call('schedule:run');

    return response('Scheduler executed');
});

// проверка команд когда на бесплатном хостинге нет Schedule
// Route::get('/run-artisan/{cmd}', function ($cmd) {
//     Artisan::call($cmd);
//     return 'Done: ' . $cmd;
// });
