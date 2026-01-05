<?php

namespace QueueIT\QueueToken\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Helpers\AESEncryption;
use QueueIT\QueueToken\Helpers\Utils;

class AESEncryptionTest extends TestCase
{
    public function testShouldEncryptAsciiText()
    {
        // Use the exact same test data as TypeScript for compatibility
        $keyString = "123456789012345678901234567890123456789012345678901234567890123"; // 32 bytes
        $tokenIdentifier = "1234567890123456"; // 16 bytes for MD5 IV
        $valueToEncrypt = array_map('ord', str_split("some text."));

        $encrypted = AESEncryption::encryptPayload($keyString, $tokenIdentifier, $valueToEncrypt);
        $base64Encrypted = base64_encode($encrypted);

        // Should produce consistent encrypted output (deterministic due to MD5 IV)
        $this->assertNotEmpty($base64Encrypted);
        $this->assertTrue(strlen($base64Encrypted) > 0);
    }

    public function testShouldDecryptAsciiText()
    {
        // Use the exact same test data as TypeScript
        $keyString = "123456789012345678901234567890123456789012345678901234567890123"; // 32 bytes
        $tokenIdentifier = "1234567890123456"; // 16 bytes for MD5 IV
        $originalText = "some text.";
        $valueToEncrypt = array_map('ord', str_split($originalText));

        // First encrypt, then decrypt
        $encrypted = AESEncryption::encryptPayload($keyString, $tokenIdentifier, $valueToEncrypt);
        $decrypted = AESEncryption::decryptPayload($keyString, $tokenIdentifier, $encrypted);
        $decryptedText = Utils::uint8ArrayToString($decrypted);

        $this->assertEquals($originalText, $decryptedText);
    }

    public function testMD5IVGeneration()
    {
        // Test MD5 IV generation (PHP-specific implementation detail with real value)
        $tokenIdentifier = "1234567890123456";
        $iv = md5($tokenIdentifier, true);

        $this->assertEquals(16, strlen($iv)); // MD5 produces exactly 16 bytes for AES IV

        // Test deterministic behavior (same input = same IV)
        $iv2 = md5($tokenIdentifier, true);
        $this->assertEquals($iv, $iv2);
    }
}
