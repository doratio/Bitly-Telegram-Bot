<?php
//https://api.telegram.org/bot380289834:AAGjd6Q0j5F_1hToz92I4bJnKK94gmQNl00/
//sendmessage?text=hello&chat_id=276921476
//&reply_markup={%22resize_keyboard%22:true,%22keyboard%22:[[%22%D0%9F%D1%80%D0%B8%D0%B2%D0%B5%D1%82%22],[%22%D0%9F%D0%BE%D0%BA%D0%B0%22]]}
require('telegram/Bot.php');
require('Log/FLogger.php');

$log = new FLogger("Log/log.txt");
$bot = new \telegram\Bot($log);

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

$log->log("создание клавиатуры");
$keyboards = [["При"], ["Пока"]];
$keyboardSettings = array(
    "resize_keyboard" => true,
);
$bot->keyboard(" f", $chat_id, $keyboards, $keyboardSettings);
$log->log("клавиатура создана");

$log->log("создание ярлыка");

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

$bot->inlineKeyboard(" f", $chat_id, $inlineKeyboards);
$log->log("ярлык создан");