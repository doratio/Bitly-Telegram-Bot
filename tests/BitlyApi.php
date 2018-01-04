<?php

require_once __DIR__ . "/../src/bitly/RestApi.php";

use PHPUnit\Framework\TestCase, bitly\RestApi;

class BitlyApi extends TestCase
{

    private $restApi;

    public function setUp()
    {
        $this->restApi = new RestApi();
    }

    public function testLinkExpand()
    {

        $expanded = $this->restApi->expand('http://bit.ly/2lU471z');

        $this->assertEquals('https://www.youtube.com/', $expanded);
    }

    public function testLinkSave()
    {
        $short = $this->restApi->createBitlink("www.youtube.com");

        $this->assertEquals('http://bit.ly/2CGmOjA', $short);
    }

    public function testAddHttp ()
    {
        $httpAdded = $this->restApi->addHttp("vk.com");

        $this->assertEquals('http://vk.com', $httpAdded);
    }


}