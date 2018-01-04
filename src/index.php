<?php
require('telegram/Bot.php');
require('bitly/RestApi.php');
require('Log/FLogger.php');

$log = new FLogger("log.txt");
$bot = new \telegram\Bot();
$bitlyApi = new bitly\RestApi();

$bitlyApi->getExistLinks();

$log->log("запрос на обновление");

$result = (array)$bot->getUpdates();
echo "<pre>";
var_dump($result);
echo "</pre>";

$chat_id = ((array)((array)((array)$result["result"][count($result["result"])-1])["message"])["chat"])["id"];
$log->log("id чата: ".$chat_id);

$log->log("запрос прошел");

$log->log("отправляем сообщение");
$bot->sendMessage("hello", $chat_id);
$log->log("сообщение отправлено");
