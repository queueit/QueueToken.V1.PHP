<?php

require_once __DIR__.'/../src/Helpers/Utils.php';

use PHPUnit\Framework\TestCase;
use QueueIT\Helpers\Utils;


class QueueITHelpersTest extends TestCase
{
    public function testPadRight()
    {
        $padded = Utils::padRight("55", '0', 4);

        $this->assertEquals("5500", $padded);
    }
}
