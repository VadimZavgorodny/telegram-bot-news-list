<?php
use Longman\TelegramBot\DB;

class Event
{
    const DAILY = 'Сегодняшние мероприятия 🕦';
    const RANDOM = 'Случайное❓';
    const SOON = 'Ожидается 🔜';
    const SOS = 'Екстренные события 🆘';

    /**
     * Get events for the current day
     *
     * @return array
     */
    public static function getDaily() {
        $pdo = DB::getPdo();//Получение обьекта PDO для работы c БД

        //SQL запрос
        $sql = 'SELECT *
                FROM event
                WHERE DATE(`date`) = CURDATE()
                ORDER BY id';

        //Выполняем SQL в виде массива
        $result = $pdo->query($sql, PDO::FETCH_ASSOC);

        //Получем данные
        return $result->fetchAll();
    }

    /**
     * Get random event
     *
     * @return array
     */
    public static function getRandom() {
        $pdo = DB::getPdo(); //Получение обьекта PDO для работы c БД

        //SQL запрос
        $sql = 'SELECT *
                FROM event AS r1
                JOIN
                  (SELECT CEIL(RAND() *
                                 (SELECT MAX(id)
                                  FROM event )) AS id) AS r2
                WHERE r1.id >= r2.id
                ORDER BY r1.id ASC
                LIMIT 1';

        //Выполняем SQL в виде массива
        $result = $pdo->query($sql, PDO::FETCH_ASSOC);

        //Получем данные
        return $result->fetchAll();
    }

    /**
     * Get emergency events
     *
     * @return array
     */
    public static function getSos() {
        $pdo = DB::getPdo(); //Получение обьекта PDO для работы c БД

        //SQL запрос
        $sql = 'SELECT *
                FROM `event`
                WHERE `type` = 2
                ORDER BY id ASC';

        //Выполняем SQL в виде массива
        $result = $pdo->query($sql, PDO::FETCH_ASSOC);

        //Получем данные
        return $result->fetchAll();
    }

    /**
     *  Get nearby Events
     *
     * @return array
     */
    public static function getSoon() {
        $pdo = DB::getPdo(); //Получение обьекта PDO для работы c БД

        //SQL запрос
        $sql = 'SELECT *
                FROM event
                WHERE date > DATE_ADD(NOW(), INTERVAL 1 DAY)
                  AND date < DATE_ADD(NOW(), INTERVAL 7 DAY)
                AND type != 2          
                ORDER BY id';

        //Выполняем SQL в виде массива
        $result = $pdo->query($sql, PDO::FETCH_ASSOC);

        //Получем данные
        return $result->fetchAll();
    }
}