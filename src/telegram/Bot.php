<?php
namespace telegram;

require('http/Connection.php');

class Bot
{

    private $connection;
    private $log;
    /**
     * Bot constructor.
     */
    public function __construct($log)
    {
        $this->log = $log;
        $telegramConfig = parse_ini_file("telegram/config.ini", false);

        $url = $telegramConfig['URL'];
        $token = $telegramConfig['token'];

        $this->connection = new \Connection($token, $url);
    }

    public function getUpdates()
    {
        return $this->connection->request("getUpdates",null);
    }

    public function sendMessage($text, $chatID)
    {
        $params["text"] = $text;
        $params["chat_id"] = $chatID;

        $this->connection->request("sendmessage", $params);
    }

    public function keyboard($text, $chatID, $keyboards, $settings)
    {
        $replyMarkup = $settings;
        $replyMarkup["keyboard"] = $keyboards;
        $this->log->log(json_encode($replyMarkup));
        $params["text"] = $text;
        $params["chat_id"] = $chatID;
        $params["reply_markup"] = json_encode($replyMarkup);

        $this->connection->request("sendmessage", $params);
    }

    public function inlineKeyboard($text, $chatID, $inlineKeyboards)
    {
        $this->log->log(json_encode($inlineKeyboards));
        $params["text"] = $text;
        $params["chat_id"] = $chatID;
        $params["reply_markup"] = json_encode($inlineKeyboards);

        $this->connection->request("sendmessage", $params);
    }
}