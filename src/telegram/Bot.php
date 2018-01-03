<?php
namespace telegram;

require('http/Connection.php');

class Bot
{

    private $connection;
    /**
     * Bot constructor.
     */
    public function __construct()
    {
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
}