<?php
// Load composer
require __DIR__ . '/vendor/autoload.php'; //Автоподключение классов
require 'application/TelegramEventBot.php'; // Поключение файла TelegramEventBot
require 'model/event.php'; //Подключение модели для работы с БД

define("BASE_PATH", dirname(__FILE__));

try {
    $telegramBot = new TelegramEventBot(); //Создаем обьект класса

    $message = $telegramBot->getUpdateMessage(); //Вызываем метод обьекта для получения обновлений

    if ($responseData = $telegramBot->generateResponseData($message)) { //Подготавливаем ответ для отправки
        $telegramBot->sendResponse($responseData); //Отправляем запрос обратно в Telegram
    }

    $telegramBot->getTelegram()->handle();
} catch (Exception $e) {
    // Silence is golden!
    // log telegram errors
    $fp = fopen('error.txt', 'a+');
    fwrite($fp, print_r($e->getMessage(), TRUE));
    fclose($fp);
    echo $e->getMessage();
}