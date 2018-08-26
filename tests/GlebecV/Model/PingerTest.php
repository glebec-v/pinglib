<?php

namespace GlebecV\Model;

use GlebecV\Model\Ping\Pinger;
use PHPUnit\Framework\TestCase;

class PingerTest extends TestCase
{
    /** @var Pinger $pinger */
    public $pinger;
    /** @var Host $host */
    public $host;

    public function setUp()/* The :void return type declaration that should be here would cause a BC issue */
    {
        $this->pinger = new Pinger();
        parent::setUp();
    }

    public function testPing()
    {
        $this->pinger->connect();

        // success ping
        $host   = new Host('127.0.0.1', null, 'localhost', null);
        $result = $this->pinger->ping($host, 1, 1, 2);
        unset($result['res']); // specific data (TTL) from every ping
        $expected = [
            'ok' => true,
            'attempts' => 1
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($result));

        // not reachable host
        $host   = new Host('128.129.130.131', null, 'localhost', null);
        $result = $this->pinger->ping($host, 1, 1, 3);
        unset($result['res']); // specific data (TTL) from every ping
        $expected = [
            'ok' => false,
            'attempts' => 3
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expected), json_encode($result));
    }

    public function testConnect()
    {
        // does not matter, connect() is empty for this class
        $this->pinger->connect('192.168.0.100', 26, '$>');
        $this->assertObjectNotHasAttribute('sourceHost', $this->pinger);
        $this->assertObjectNotHasAttribute('port', $this->pinger);
        $this->assertObjectNotHasAttribute('prompt', $this->pinger);
    }
}
