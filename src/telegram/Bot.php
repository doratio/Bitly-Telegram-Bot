<?php
namespace telegram;

require('telegram/Connection.php');
require('Log/FLogger.php');
require('TIniFileEx.php');

class Bot
{

    private $connection;
    private $log;
    private $config;
    private $configTelegram;
    private $lastupdate;

    /**
     * Bot constructor.
     */
    public function __construct()
    {
        $this->config = new \TIniFileEx("config.ini");
        $this->configTelegram = new \TIniFileEx("telegram/config.ini");

        $this->log = new \FLogger($this->config->read('main', "logFile"));
        $this->lastupdate = $this->config->read('main', "lastUpdateID");

        $this->connection = new Connection(
            $this->configTelegram->read('telegram', 'token'),
            $this->configTelegram->read('telegram', 'URL')
        );
    }

    public function getUpdates($offset)
    {
        $params = null;
        if (!empty($offset)) {
            $params["offset"] = $offset;
        }
        return $this->connection->request("getUpdates", $params);
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

    public function run()
    {
        while (true) {
            $this->log->log("запрос на обновление");
            $updates = ((array)$this->getUpdates($this->lastupdate))["result"];
            $this->log->log("запрос прошел");

            echo "<pre>";
            var_dump($updates);
            echo "</pre>";
            $chat_id = null;
            $chat = null;
            $message = null;
            $update_id = null;

            foreach ($updates as $update) {
                $update_id = ((array)$update)["update_id"];
                $chat = ((array)((array)$update)["message"])["chat"];
                $chat_id = ((array)$chat)["id"];
                $this->log->log("id чата: " . $chat_id);
                $message = ((array)((array)$update)["message"])["text"];

                switch ($message) {
                    case "/start":
                        $this->log->log("создание клавиатуры");
                        $keyboards = [["История"], ["Помощь"]];
                        $keyboardSettings = array(
                            "resize_keyboard" => true,
                        );
                        $this->keyboard(" f", $chat_id, $keyboards, $keyboardSettings);
                        $this->log->log("клавиатура создана");
                        break;

                    case "История":
                        $this->log->log("создание ярлыка");

                        $inlineKeyboards = [
                            "inline_keyboard" => [
                                [
                                    (object)[
                                        "text" => "<",
                                        "callback_data" => "google.com"
                                    ],
                                    (object)[
                                        "text" => ">",
                                        "callback_data" => "google.com"
                                    ]
                                ]
                            ]
                        ];

                        $this->inlineKeyboard("История", $chat_id, $inlineKeyboards);
                        $this->log->log("ярлык создан");
                        break;

                    case "/help":
                    case "Помощь":
                        $this->log->log("отправляем сообщение");
                        $this->sendMessage("помощь", $chat_id);
                        $this->log->log("сообщение отправлено");
                        break;

                    default:
                        $this->log->log("отправляем сообщение");
                        $this->sendMessage("text", $chat_id);
                        $this->log->log("сообщение отправлено");
                }
            }

            $this->lastupdate = $update_id+1;
            if ($this->lastupdate > 1) {
                $this->config->write('main', "lastUpdateID", $this->lastupdate);
                $this->config->updateFile();
            }

            sleep(1);
        }
    }
}