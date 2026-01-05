<?php

namespace QueueIT\QueueToken\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Helpers\Utils;

class UtilsTest extends TestCase
{
    public function testPadRight()
    {
        $padded = Utils::padRight("55", '0', 4);
        $this->assertEquals("5500", $padded);
    }

    public function testUint8ArrayToHexString()
    {
        $byteArray = [255, 0, 128, 16];
        $hexString = Utils::uint8ArrayToHexString($byteArray);
        $this->assertEquals("ff008010", $hexString);
    }

    public function testUint8ArrayToString()
    {
        $byteArray = [72, 101, 108, 108, 111]; // "Hello" in ASCII
        $string = Utils::uint8ArrayToString($byteArray);
        $this->assertEquals("Hello", $string);
    }

    public function testStringToUint8Array()
    {
        // This function URL-encodes first, so test basic functionality
        $byteArray = Utils::stringToUint8Array("Hello");
        $this->assertIsArray($byteArray);
        $this->assertNotEmpty($byteArray);

        // Test consistency
        $byteArray2 = Utils::stringToUint8Array("Hello");
        $this->assertEquals($byteArray, $byteArray2);
    }
}
