<?php
require_once('src/http/Connection.php');

$telegramConfig = parse_ini_file("src/telegram/config.ini", false);

$url = $telegramConfig['URL'];
$token = $telegramConfig['token'];

$bot = new Connection($token, $url);
$bot->request("getUpdates",null);