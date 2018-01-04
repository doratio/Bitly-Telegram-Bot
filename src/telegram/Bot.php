<?php

namespace telegram;

use bitly\RestApi;
use exceptions\FormatException;
use exceptions\ShortLinkNotFoundedException;

require('telegram/Connection.php');
require('Log/FLogger.php');
require('TIniFileEx.php');
require('bitly/RestApi.php');
require_once("exceptions/FormatException.php");

class Bot
{

    private $connection;
    private $log;
    private $config;
    private $configTelegram;
    private $lastupdate;
    private $users;
    private $bitlyApi;
    private $historyindex;

    /**
     * Bot constructor.
     */
    public function __construct()
    {
        $this->bitlyApi = new RestApi();
        $this->users = new \TIniFileEx("Log/users.ini");
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
        $params["disable_web_page_preview"] = true;

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
        $params["disable_web_page_preview"] = true;

        $this->connection->request("sendmessage", $params);
    }

    public function sendLink($message, $chat_id)
    {
        try {
            if (preg_match('/^(https?:\/\/)?([\w\.]+)\.([a-z]{2,6}\.?)(\/[\w\.]*)*\/?$/', $message)) {
                $this->sendMessage($this->bitlyApi->createBitlink($message), $chat_id);
            } else if (
                preg_match('/^(https?:\/\/)?bit\.ly(\/[\w\.]*)*\/?$/', $message) ||
                preg_match('/https?:\/\/j\.mp(\/[\w\.]*)*\/?$/', $message)
            ) {
                $this->sendMessage($this->bitlyApi->expand($message), $chat_id);
            } else {
                throw new FormatException('Неверный формат ссылки');
            }
        } catch (ShortLinkNotFoundedException $e) {
            $this->sendMessage($e->getMessage(), $chat_id);
        } catch (FormatException $e) {
            $this->sendMessage($e->getMessage(), $chat_id);
        }
    }

    public function editMessageText($message, $chat_id, $message_id, $inlineKeyboards)
    {
        $params["chat_id"] = $chat_id;
        $params["message_id"] = $message_id;
        $params["text"] = $message;
        $params["disable_web_page_preview"] = true;
        $params["reply_markup"] = json_encode($inlineKeyboards);

        $this->connection->request("editMessageText", $params);
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
                if (((array)$update)["callback_query"] === null) {
                    $have_callback_query = false;
                    $chat = ((array)((array)$update)["message"])["chat"];
                    $chat_id = ((array)$chat)["id"];
                    $this->log->log("id чата: " . $chat_id);
                    $message = ((array)((array)$update)["message"])["text"];
                    $user = ((array)((array)((array)$update)["message"])["from"]);
                } else {
                    $have_callback_query = true;
                    $callback_query = ((array)$update)["callback_query"];
                    $message_id = ((array)((array)$callback_query)["message"])['message_id'];
                    $chat_id = ((array)((array)((array)$callback_query)["message"])["chat"])["id"];
                    $user = ((array)((array)((array)$callback_query)["message"])["from"]);
                    $message = ((array)$callback_query)["data"];
                    if ($this->historyindex["$message_id"] === null) {
                        $this->historyindex["$message_id"]["offset"] = 0;
                    }
                }

                switch ($message) {
                    case "/start":
                        if ($user["id"] != null &&
                            $user["id"] != null &&
                            $this->users->read($user["id"], "chat_id", null) != $chat_id) {

                            $this->users->write($user["id"], "first_name", $user["first_name"]);
                            $this->users->write($user["id"], "last_name", $user["last_name"]);
                            $this->users->write($user["id"], "username", $user["username"]);
                            $this->users->write($user["id"], "language", $user["language_code"]);
                            $this->users->write($user["id"], "chat_id", $chat_id);

                            $this->users->updateFile();
                        }

                        $this->log->log("создание клавиатуры");
                        $keyboards = [["История"], ["Помощь"]];
                        $keyboardSettings = array(
                            "resize_keyboard" => true,
                        );
                        $this->keyboard("1) Как сократить URL?\nДля сокращения URL, отправьте ссылку сообщением боту.\nВ ответ он пришлет Вам сокращенную ссылку.\n2) Как расшифровать сокращенную ссылку?\nДля расшифровки сокращенной ссылки (например, bit.ly/2CF5z2o), отправьте ее сообщением боту.\nВ ответ он пришлет Вам исходную ссылку.\n3) Как посмотреть историю созданных сокращенных ссылок?\nНажмите на кнопку \"История\", снизу от поля ввода чата.\nВам придет список последних ссылок. Для навигации используйте кнопки навигации. ", $chat_id, $keyboards, $keyboardSettings);
                        $this->log->log("клавиатура создана");
                        break;

                    case "История":
                        $this->log->log("создание ярлыка");

                        $history = $this->bitlyApi->getExistLinks();
                        $content = "История созданных ссылок: \n\n";
                        foreach ($history as $i) {
                            $content .= $i['title'] . "\n";
                            $content .= "🔗 " . $i['long_url'] . "\n\n" . "➡ " . $i['link'] . "\n---------\n";
                        }

                        $inlineKeyboards = [
                            "inline_keyboard" => [
                                [
                                    (object)[
                                        "text" => "<",
                                        "callback_data" => "prev"
                                    ],
                                    (object)[
                                        "text" => ">",
                                        "callback_data" => "next"
                                    ]
                                ]
                            ]
                        ];

                        $this->inlineKeyboard($content, $chat_id, $inlineKeyboards);
                        $this->log->log("ярлык создан");
                        break;

                    case "/help":
                    case "Помощь":
                        $this->log->log("отправляем сообщение");
                        $this->sendMessage("1) Как сократить URL?\nДля сокращения URL, отправьте ссылку сообщением боту.\nВ ответ он пришлет Вам сокращенную ссылку.\n2) Как расшифровать сокращенную ссылку?\nДля расшифровки сокращенной ссылки (например, bit.ly/2CF5z2o), отправьте ее сообщением боту.\nВ ответ он пришлет Вам исходную ссылку.\n3) Как посмотреть историю созданных сокращенных ссылок?\nНажмите на кнопку \"История\", снизу от поля ввода чата.\nВам придет список последних ссылок. Для навигации используйте кнопки навигации. ", $chat_id);
                        $this->log->log("сообщение отправлено");
                        break;

                    case "prev":
                        if ($have_callback_query) {

                            if($this->historyindex["$message_id"]["offset"] !== 0) {
                                $this->historyindex["$message_id"]["offset"] -= 3;
                            }

                            echo "<div style='color: red'>".$this->historyindex["$message_id"]["offset"]."</div>";

                            $history = $this->bitlyApi->getExistLinks(
                                $this->historyindex["$message_id"]["offset"]
                            );

                            $content = "История созданных ссылок: \n\n";
                            foreach ($history as $i) {
                                $content .= $i['title'] . "\n";
                                $content .= "🔗 " . $i['long_url'] . "\n\n" . "➡ " . $i['link'] . "\n---------\n";
                            }

                            $inlineKeyboards = [
                                "inline_keyboard" => [
                                    [
                                        (object)[
                                            "text" => "<",
                                            "callback_data" => "prev"
                                        ],
                                        (object)[
                                            "text" => ">",
                                            "callback_data" => "next"
                                        ]
                                    ]
                                ]
                            ];

                            $this->editMessageText($content, $chat_id, $message_id, $inlineKeyboards);
                        } else {
                            $this->sendMessage('Неверный формат ссылки', $chat_id);
                        }
                        break;
                    case "next":
                        if ($have_callback_query) {

                                $this->historyindex["$message_id"]["offset"] += 3;
                                echo "<div style='color: red'>".$this->historyindex["$message_id"]["offset"]."</div>";
                            try {
                                $history = $this->bitlyApi->getExistLinks(
                                    $this->historyindex["$message_id"]["offset"]
                                );
                            } catch (\Exception $ex) {
                                $this->historyindex["$message_id"]["offset"] -= 3;
                                break ;
                            }
                            if(count($history) == 0)
                            $content = "История созданных ссылок: \n\n";
                            foreach ($history as $i) {
                                $content .= $i['title'] . "\n";
                                $content .= "🔗 " . $i['long_url'] . "\n\n" . "➡ " . $i['link'] . "\n---------\n";
                            }

                            $inlineKeyboards = [
                                "inline_keyboard" => [
                                    [
                                        (object)[
                                            "text" => "<",
                                            "callback_data" => "prev"
                                        ],
                                        (object)[
                                            "text" => ">",
                                            "callback_data" => "next"
                                        ]
                                    ]
                                ]
                            ];

                            $this->editMessageText($content, $chat_id, $message_id, $inlineKeyboards);
                        } else {
                            $this->sendMessage('Неверный формат ссылки', $chat_id);
                        }
                        break;
                    default:
                        $this->log->log("отправляем сообщение");
                        $this->sendLink($message, $chat_id);
                        $this->log->log("сообщение отправлено");
                }
            }

            $this->lastupdate = $update_id + 1;
            if ($this->lastupdate > 1) {
                $this->config->write('main', "lastUpdateID", $this->lastupdate);
                $this->config->updateFile();
            }
            sleep(1);
        }
    }
}
