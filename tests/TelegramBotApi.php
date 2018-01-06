<?php

use PHPUnit\Framework\TestCase, telegram\BotApi;

require_once __DIR__ . "/../src/telegram/BotApi.php";

class TelegramBotApi extends TestCase
{
    private $bot;

    public function setUp()
    {
        $this->bot = new BotApi();
    }

    public function testgetUpdates()
    {
        $data = $this->bot->getUpdates()["ok"];
        echo($data);

        $this->assertEquals(true, $data);
    }
}
