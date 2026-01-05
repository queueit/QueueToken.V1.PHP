<?php

namespace QueueIT\QueueToken\Tests;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Helpers\Utils;

class QueueITHelpersTest extends TestCase
{
    public function testPadRight()
    {
        $padded = Utils::padRight("55", '0', 4);

        $this->assertEquals("5500", $padded);
    }
}
