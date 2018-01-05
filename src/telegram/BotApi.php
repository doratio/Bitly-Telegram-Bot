<?php

namespace telegram;

require_once(__DIR__ . "/Connection.php");
require_once(__DIR__ . "/../TIniFileEx.php");

class BotApi
{

    /**
     *
     * Экземпляр класса Connection
     *
     * Предоставляет общие методы для работы
     * Telegram bot API
     *
     * @var Connection
     */
    private $connection;

    /**
     * @var \TIniFileEx
     */
    private $configTelegram;

    /**
     * BotApi конструктор.
     *
     * Создает экземпляр Connection
     *
     */
    public function __construct()
    {
        $this->configTelegram = new \TIniFileEx("../src/telegram/config.ini");

        $this->connection = new Connection(
            $this->configTelegram->read('telegram', 'token'),
            $this->configTelegram->read('telegram', 'URL')
        );
    }

    /**
     *
     * Возвращает все последние обновления
     *
     * @param $offset Отступ
     * @return array Массив ответа
     */
    public function getUpdates($offset = null)
    {
        $params = null;
        if (!empty($offset)) {
            $params["offset"] = $offset;
        }
        return $this->connection->request("getUpdates", $params);
    }

    /**
     *
     * Отправляет сообщение пользователю
     *
     * @param string $text Тест сообщения
     * @param string $chatID Идентификатор чата
     */
    public function sendMessage($text, $chatID)
    {
        $params["text"] = $text;
        $params["chat_id"] = $chatID;
        $params["disable_web_page_preview"] = true;

        $this->connection->request("sendmessage", $params);
    }

    /**
     *
     * Выводит кнопки пользователю
     *
     * @param string $text Текст сообщения ообщение
     * @param string $chatID Идентификатор чата
     * @param array $keyboards Массив кнопок
     * @param array $settings Массив настроек
     */
    public function keyboard($text, $chatID, $keyboards, $settings)
    {
        $replyMarkup = $settings;
        $replyMarkup["keyboard"] = $keyboards;
        $params["disable_web_page_preview"] = true;
        $params["text"] = $text;
        $params["chat_id"] = $chatID;
        $params["reply_markup"] = json_encode($replyMarkup);

        $this->connection->request("sendmessage", $params);
    }

    /**
     *
     * Выводит кнопки в сообщении
     *
     * @param string $text Текст кнопки
     * @param string $chatID Идентификатор чата
     * @param string $inlineKeyboards Массив кнопок
     */
    public function inlineKeyboard($text, $chatID, $inlineKeyboards)
    {
        $params["text"] = $text;
        $params["chat_id"] = $chatID;
        $params["reply_markup"] = json_encode($inlineKeyboards);
        $params["disable_web_page_preview"] = true;

        $this->connection->request("sendmessage", $params);
    }

    /**
     *
     * Редактирование сообщения
     *
     * @param string $message новый текст
     * @param string $chat_id Идентификатор чата
     * @param string $message_id Идентификатор сообщения
     * @param string $inlineKeyboards Массив кнопок
     */
    public function editMessageText($message, $chat_id, $message_id, $inlineKeyboards)
    {
        $params["chat_id"] = $chat_id;
        $params["message_id"] = $message_id;
        $params["text"] = $message;
        $params["disable_web_page_preview"] = true;
        $params["reply_markup"] = json_encode($inlineKeyboards);

        $this->connection->request("editMessageText", $params);
    }


}