<?php

require('telegram/Bot.php');

$bot = new \telegram\Bot();
$bot->run();



//
//$log->log("создание клавиатуры");
//$keyboards = [["При"], ["Пока"]];
//$keyboardSettings = array(
//    "resize_keyboard" => true,
//);
//$bot->keyboard(" f", $chat_id, $keyboards, $keyboardSettings);
//$log->log("клавиатура создана");
//
;