<?php
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Update;

class TelegramEventBot
{
    /**
     * @var object
     */
    private $telegram;

    /**
     * @var array
     */
    private $config;

    /**
     * Get telegram object
     *
     * @return \Longman\TelegramBot\Telegram|object
     */
    public function getTelegram() {
        return $this->telegram;
    }

    /**
     * TelegramEventBot constructor.
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function __construct() {
        $this->config = include('config/config.php'); //Подключаем конфигурационный файл
        $this->telegram = new Longman\TelegramBot\Telegram($this->config['telegramBot']['botApiKey'], $this->config['telegramBot']['botUserName']); //Обьект телеграм бота
        $this->telegram->enableMySql($this->config['db']); //Подключаем Mysql предварительно ее создав на хостинге
        $this->telegram->addCommandsPaths( [BASE_PATH . '/Commands']); //Путь для подключения команд https://github.com/php-telegram-bot/core#commands
        $this->telegram->setUploadPath('upload'); //Папке для загруженных файлов
    }

    /**
     * Get incoming message object
     *
     * @return \Longman\TelegramBot\Entities\Message
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function getUpdateMessage() {
        $data = json_decode(Request::getInput(), true); //Получаем входящий запрос и преобразует в json

        $oUpdate = new Update($data, $this->config); //Создаем обьект класса Update
        return $oUpdate->getMessage(); //Получаем входящее сообщение из Telegram
    }

    /**
     * Generate response data
     *
     * @param object $message
     * @return array|boolean
     */
    public function generateResponseData($message) {
        if (!$message->getCommand()) { //Если сообщение не является командой
            $responseData = []; //Инициализируем пустой  массив
            $result = NULL; // Инициализируем пусту переменную с результатом

            $chat_id = $message->getChat()->getId(); //Получает id чата откуда было прислано сообщение

            switch ($message->getText()) { //Проверяем какое сообщение было прислано
                case Event::DAILY:
                    $result = Event::getDaily(); //Вызов модели для получения данных за день
                    break;
                case Event::SOON:
                    $result = Event::getSoon(); //Вызов модели для получения данных за ближайщее время
                    break;
                case Event::RANDOM:
                    $result = Event::getRandom(); //Вызов модели для получения рандомного значения
                    break;
                case Event::SOS:
                    $result = Event::getSos(); //Вызов модели для получения экстренных событий
                    break;
            }

            if ($result) {
                foreach ($result as $key => $item) { //Интерируеv полученные данные
                    /**
                     * Заполняем массив для ответа сообщениеями
                     */
                    $responseData[$key]['message'] = [
                        'chat_id' => $chat_id,
                        'text' => $item['description'],
                        'parse_mode' => 'HTML'
                    ];

                    /**
                     * Заполняем массив для ответа фотографиями
                     */
                    $responseData[$key]['photo'] = [
                        'chat_id' => $chat_id,
                        'photo' => Request::encodeFile($this->telegram->getUploadPath() . '/' . $item['image'])
                    ];
                }
            } else { //Если данные не были полученны из базы
                $responseData[]['message'] = [
                    'chat_id' => $chat_id,
                    'text' => 'Записей нет',
                    'parse_mode' => 'HTML'
                ];
            }

            return $responseData; //Возвращаем данные из метода
        }
        return false;
    }

    /**
     * Send response to telegram
     *
     * @param array $responseData
     */
    public function sendResponse(array $responseData) {
        foreach ($responseData as $key=>$item) { //Итерируем данные
            if (array_key_exists('message', $item)) { //Если массив содеражит ключ message отправляем текстовое сообщение
                    Request::sendMessage($item['message']); //Отправляем сообщение
            }

            if (array_key_exists('photo', $item)) { //Если массив содеражит ключ photo отправляем фото
                Request::sendPhoto($item['photo']); //Отправляем фото
            }
        }
    }
}