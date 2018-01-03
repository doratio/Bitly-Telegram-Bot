<?php
require('telegram/Bot.php');
require('Log/FLogger.php');

$log = new FLogger("log.txt");
$bot = new \telegram\Bot();

$log->log("запрос на обновление");
echo "<pre>";
var_dump($bot->getUpdates());
echo "</pre>";
$log->log("запрос прошел");

$log->log("отправляем сообщение");
$bot->sendMessage("hello");
$log->log("сообщение отправлено");