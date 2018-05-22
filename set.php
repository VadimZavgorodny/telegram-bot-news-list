<?php
// Load composer
require __DIR__ . '/vendor/autoload.php';

$bot_api_key  = ''; //Ключ от папы бота
$bot_username = ''; //Имя бота
$hook_url = 'https://telegram-test-bot.tk/hook.php'; //Пусть к хук файлу

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url); //Устанавливаем хук
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    // echo $e->getMessage();
}