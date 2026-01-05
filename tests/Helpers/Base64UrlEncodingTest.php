<?php

namespace QueueIT\QueueToken\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Helpers\Base64UrlEncoding;

class Base64UrlEncodingTest extends TestCase
{
    public function testShouldEncodeNormalAsciiText()
    {
        $encoded = Base64UrlEncoding::encode("SomeText");

        $this->assertNotNull($encoded);
        $this->assertEquals("U29tZVRleHQ", $encoded);
    }

    public function testShouldEncodeUtf8Text()
    {
        $encoded = Base64UrlEncoding::encode("14.95 €");

        $this->assertNotNull($encoded);
        $this->assertEquals("MTQuOTUg4oKs", $encoded);
    }

    public function testShouldEncodeUtf8Text2()
    {
        $encoded = Base64UrlEncoding::encode("⌷←⍳→⍴∆∇⊃‾⍎⍕⌈ 14.95 €");

        $this->assertNotNull($encoded);
        $this->assertEquals("4oy34oaQ4o2z4oaS4o204oiG4oiH4oqD4oC-4o2O4o2V4oyIIDE0Ljk1IOKCrA", $encoded);
    }

    public function testShouldDecodeNormalAsciiText()
    {
        $decoded = Base64UrlEncoding::decode("U29tZVRleHQ");

        $this->assertNotNull($decoded);
        $this->assertEquals('SomeText', $decoded);
        $this->assertEquals(8, strlen($decoded));
    }

    public function testShouldDecodeNormalUtf8Text()
    {
        $decoded = Base64UrlEncoding::decode("MTQuOTUg4oKs");

        $this->assertNotNull($decoded);
        $this->assertEquals('14.95 €', $decoded);
        $this->assertEquals(9, strlen($decoded));
    }

    public function testShouldDecodeNormalUtf8Text2()
    {
        $decoded = Base64UrlEncoding::decode("4oy34oaQ4o2z4oaS4o204oiG4oiH4oqD4oC-4o2O4o2V4oyIIDE0Ljk1IOKCrA");

        $this->assertNotNull($decoded);
        $this->assertEquals('⌷←⍳→⍴∆∇⊃‾⍎⍕⌈ 14.95 €', $decoded);
        $this->assertEquals(46, strlen($decoded));
    }

    // URL-Safe Specific Test (PHP Base64URL differentiator)
    public function testUrlSafeEncoding()
    {
        // Test with data that produces URL-unsafe characters in standard base64
        $testData = "This is a test string with special chars: +/=";

        $encoded = Base64UrlEncoding::encode($testData);
        $decoded = Base64UrlEncoding::decode($encoded);

        // Should round-trip correctly
        $this->assertEquals($testData, $decoded);

        // Should not contain URL-unsafe characters (key differentiator from standard Base64)
        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }
}
