<?php

namespace GlebecV\Repository;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class CsvRepositoryTest extends TestCase
{
    protected $file;

    public function setUp()
    {
        vfsStream::setup('home');
        $this->file = vfsStream::url('home/test.json');
    }

    public function testGetHostCollection()
    {
        $this->assertTrue(true);
    }

    public function testSetup()
    {
        $this->assertTrue(true);
    }
}
