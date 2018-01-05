<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../src/Log/FLogger.php";

class LoggerTest extends TestCase
{

    private $flag;

    public function setUp()
    {
        $this->log = new \FLogger("test.txt");
    }

    public function testlog()
    {
        $this->log->log("ok");
        $this->log = null;

        $fp = fopen('test.txt', 'r');

        fseek($fp, 0, SEEK_SET);
        $i = 0;
        while ($i < 1000)    // защитимся на всякий случай
        {
            if (feof($fp)) {
                $this->flag = false;
                break;
            } else {
                $this->flag = true;
            }

            echo '<br>';
            $i++;
        }

        $this->assertEquals(true, $this->flag);
    }
}
