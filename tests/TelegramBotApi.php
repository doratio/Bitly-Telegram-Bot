<?php

require_once __DIR__ . "/../src/telegram/BotApi.php";

use PHPUnit\Framework\TestCase, telegram\BotApi;

class TelegramBotApi extends TestCase
{
    private $bot;

    public function setUp()
    {
        $this->bot = new BotApi();
    }

    public function testgetUpdates()
    {
        $data = ((array)$this->bot->getUpdates("895121355"))["ok"];
        echo($data);

        $this->assertEquals(true, $data);
    }
}
