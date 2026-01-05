<?php

namespace QueueIT\QueueToken\Helpers;

use QueueIT\QueueToken\Helpers\ShaHashing;
use Exception;

class AESEncryption
{
    private const CIPHER = 'aes-256-cbc';

    public static function encryptPayload(string $secretKey, string $tokenIdentifier, array $valueToEncrypt): string
    {
        $key = self::deriveKey($secretKey);
        $iv = self::generateIV($tokenIdentifier);
        $data = pack('C*', ...$valueToEncrypt);

        $encryptedData = openssl_encrypt($data, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encryptedData === false) {
            throw new Exception("Encryption failed.");
        }

        return $encryptedData;
    }

    public static function decryptPayload(string $secretKey, string $tokenIdentifier, string $encryptedData): array
    {
        $key = self::deriveKey($secretKey);
        $iv = self::generateIV($tokenIdentifier);

        $decryptedData = openssl_decrypt($encryptedData, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decryptedData === false) {
            throw new Exception("Decryption failed.");
        }

        return array_values(unpack('C*', $decryptedData));
    }

    private static function deriveKey(string $secretKey): string
    {
        return ShaHashing::generateHash($secretKey, '');
    }

    private static function generateIV(string $tokenIdentifier): string
    {
        // Safe to use deterministic IV since tokenIdentifier is unique per token
        return md5($tokenIdentifier, true);
    }
}
