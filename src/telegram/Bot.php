<?php

namespace telegram;

use bitly\RestApi;
use exceptions\FormatException;
use exceptions\ShortLinkNotFoundedException;

require('Log/FLogger.php');
require('TIniFileEx.php');
require('bitly/RestApi.php');
require_once("exceptions/FormatException.php");
require_once('telegram/BotApi.php');

/**
 * Class Bot
 *
 * Предоставляет методы доступа к чат-боту
 *
 * @package telegram Содержит все классы для работы с Telegram API
 */
class Bot
{

    /**
     * @var \FLogger Логгер
     */
    private $log;
    /**
     * @var \TIniFileEx
     */
    private $config;
    /**
     * @var string
     */
    private $lastupdate;
    /**
     * @var \TIniFileEx
     */
    private $users;
    /**
     * @var RestApi
     */
    private $bitlyApi;
    /**
     * @var
     */
    private $historyindex;
    /**
     * @var array кнопки
     */
    private $inlineKeyboards;
    /**
     * @var string
     */
    private $tutorial = "Помощь\n\n1) Как сократить URL?\nДля сокращения URL, отправьте ссылку сообщением боту.\nВ ответ он пришлет Вам сокращенную ссылку.\n2) Как расшифровать сокращенную ссылку?\nДля расшифровки сокращенной ссылки (например, bit.ly/2CF5z2o), отправьте ее сообщением боту.\nВ ответ он пришлет Вам исходную ссылку.\n3) Как посмотреть историю созданных сокращенных ссылок?\nНажмите на кнопку \"История\", снизу от поля ввода чата.\nВам придет список последних ссылок. Для навигации используйте кнопки навигации.\n\nДля вызова этой инструкции нажмите на кнопку \"Помощь\" снизу от поля ввода.";


    /**
     * Bot constructor.
     *
     * Выполняет основные инициализации
     *
     */
    public function __construct()
    {
        $this->inlineKeyboards = [
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
        $this->bitlyApi = new RestApi();
        $this->botApi = new BotApi();

        $this->users = new \TIniFileEx("Log/users.ini");
        $this->config = new \TIniFileEx("config.ini");

        $this->log = new \FLogger($this->config->read('main', "logFile"));
        $this->lastupdate = $this->config->read('main', "lastUpdateID");
    }

    /**
     *
     * Обрабатывет отправленную ссылку
     *
     * @param string $message Текст сообщения
     * @param string $chat_id Идентификатор чата
     */
    public function sendLink($message, $chat_id)
    {
        try {
            if (
                preg_match('/^(https?:\/\/)?bit\.ly(\/[\w\.]*)*\/?$/', $message) ||
                preg_match('/^(https?:\/\/)?j\.mp(\/[\w\.]*)*\/?$/', $message)
            ) {
                $this->botApi->sendMessage($this->bitlyApi->expand($message), $chat_id);
            } else if (preg_match('/^(https?:\/\/)?([\w\.]+)\.([a-z]{2,6}\.?)(\/[\w&?=\-\.]*)*\/?$/', $message)) {
                $this->botApi->sendMessage($this->bitlyApi->createBitlink($message), $chat_id);
            } else {
                throw new FormatException('Неверный формат ссылки');
            }
        } catch (ShortLinkNotFoundedException $e) {
            $this->botApi->sendMessage($e->getMessage(), $chat_id);
        } catch (FormatException $e) {
            $this->botApi->sendMessage($e->getMessage(), $chat_id);
        }
    }

    /**
     *
     * Возвращает отформатированную строку списка истории
     *
     * @param array $history Массив ссылок
     * @return string Отформатированная строка со списком ссылок
     */
    private function renderHistory($history)
    {
        $content = "История созданных ссылок: \n\n";
        foreach ($history as $i) {
            $content .= $i['title'] . "\n";
            $content .= "🔗 " . $i['long_url'] . "\n\n" . "➡ " . $i['link'] . "\n---------\n";
        }
        return $content;
    }

    /**
     * Запускает чат-бота
     */
    public function run()
    {
        while (true) {
            $this->log->log("запрос на обновление");
            $updates = $this->botApi->getUpdates($this->lastupdate)["result"];
            $this->log->log("запрос прошел");

            echo "<pre>";
            var_dump($updates);
            echo "</pre>";

            $chat_id = null;
            $chat = null;
            $message = null;
            $update_id = null;

            foreach ($updates as $update) {
                $update_id = $update["update_id"];
                if ($update["callback_query"] === null) {
                    $have_callback_query = false;
                    $chat = $update["message"]["chat"];
                    $chat_id = $chat["id"];
                    $this->log->log("id чата: " . $chat_id);
                    $message = $update["message"]["text"];
                    $user = $update["message"]["from"];
                } else {
                    $have_callback_query = true;
                    $callback_query = $update["callback_query"];
                    $message_id = $callback_query["message"]['message_id'];
                    $chat_id = $callback_query["message"]["chat"]["id"];
                    $user = $callback_query["message"]["from"];
                    $message = $callback_query["data"];
                    if ($this->historyindex["$message_id"] === null) {
                        $this->historyindex["$message_id"]["offset"] = 0;
                    }
                }

                switch ($message) {
                    case "/start":
                        $this->infoAboutU($user, $chat_id);

                        $this->log->log("создание клавиатуры");
                        $keyboards = [["История"], ["Помощь"]];
                        $keyboardSettings = array(
                            "resize_keyboard" => true,
                        );
                        $this->botApi->keyboard($this->tutorial, $chat_id, $keyboards, $keyboardSettings);
                        $this->log->log("клавиатура создана");
                        break;

                    case "История":
                        $this->log->log("создание ярлыка");

                        $history = $this->bitlyApi->getExistLinks();

                        $content = $this->renderHistory($history);

                        $this->botApi->inlineKeyboard($content, $chat_id, $this->inlineKeyboards);
                        $this->log->log("ярлык создан");
                        break;

                    case "/help":
                    case "Помощь":
                        $this->log->log("отправляем сообщение");
                        $this->botApi->sendMessage($this->tutorial, $chat_id);
                        $this->log->log("сообщение отправлено");
                        break;

                    case "prev":
                        if ($have_callback_query) {

                            if ($this->historyindex["$message_id"]["offset"] !== 0) {
                                $this->historyindex["$message_id"]["offset"] -= 3;
                            }

                            $history = $this->bitlyApi->getExistLinks(
                                $this->historyindex["$message_id"]["offset"]
                            );

                            $content = $this->renderHistory($history);

                            $this->botApi->editMessageText($content, $chat_id, $message_id, $this->inlineKeyboards);
                        } else {
                            $this->botApi->sendMessage('Неверный формат ссылки', $chat_id);
                        }
                        break;

                    case "next":
                        if ($have_callback_query) {

                            $this->historyindex["$message_id"]["offset"] += 3;

                            try {
                                $history = $this->bitlyApi->getExistLinks(
                                    $this->historyindex["$message_id"]["offset"]
                                );
                            } catch (\Exception $ex) {
                                $this->historyindex["$message_id"]["offset"] -= 3;
                                break;
                            }

                            $content = $this->renderHistory($history);

                            $this->botApi->editMessageText($content, $chat_id, $message_id, $this->inlineKeyboards);
                        } else {
                            $this->botApi->sendMessage('Неверный формат ссылки', $chat_id);
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

    /**
     *
     * Получает информацию о пользователях
     *
     * @param array $user информация о пользователе
     * @param string $chat_id Идентификатор чата
     */
    public function infoAboutU($user, $chat_id)
    {
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
    }
}
