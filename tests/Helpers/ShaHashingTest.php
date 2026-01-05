<?php

namespace QueueIT\QueueToken\Tests\Helpers;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Helpers\ShaHashing;
use QueueIT\QueueToken\Helpers\Utils;

class ShaHashingTest extends TestCase
{
    public function testShouldHashLongValues()
    {
        // This is the key test from TypeScript that validates the actual implementation
        $key = "5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6";
        $text = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1Mzc0MDE2MDAwMDAsImV4cCI6MTU0MTgwODAwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50IiwiaXAiOiI1LjcuOC42IiwieGZmIjoiNDUuNjcuMi40LDM0LjU2LjMuMiJ9.";

        $hashedValue = ShaHashing::generateHash($key, $text);
        $hexString = Utils::uint8ArrayToHexString(array_values(unpack('C*', $hashedValue)));

        $this->assertEquals("32bafc1c2af17afd86b931a414595220243526251282da1c68d75c59499dde73", $hexString);
    }

    public function testGenerateHashMethod()
    {
        // Test the core ShaHashing::GenerateHash method functionality
        $secretKey = "mySecretKey";
        $tokenString = "myTokenString";

        $hash = ShaHashing::generateHash($secretKey, $tokenString);

        // Hash should be 32 bytes (SHA256)
        $this->assertEquals(32, strlen($hash));

        // Different inputs should produce different hashes
        $differentHash = ShaHashing::generateHash($secretKey, "differentTokenString");
        $this->assertNotEquals($hash, $differentHash);
    }
}
