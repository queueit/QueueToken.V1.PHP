<?php

namespace QueueIT\QueueToken\Tests;

use QueueIT\QueueToken\Helpers\Base64UrlEncoding;
use QueueIT\QueueToken\Helpers\ShaHashing;
use QueueIT\QueueToken\Models\EncryptionType;
use QueueIT\QueueToken\Models\TokenVersion;

/**
 * Builds tokens with a valid signature but hand-made contents, to test what parsing does with them.
 */
class SignedTokens
{
    public static function encodeHeader(array $header): string
    {
        return Base64UrlEncoding::encode(json_encode($header));
    }

    public static function sign(string $tokenWithoutHash): string
    {
        $hash = ShaHashing::generateHash(SampleTokenValues::$secretKey, $tokenWithoutHash);
        return $tokenWithoutHash . "." . Base64UrlEncoding::encode($hash);
    }

    /**
     * A token with a valid header and signature whose payload is not AES cipher text.
     */
    public static function withUndecryptablePayload(): string
    {
        $header = self::encodeHeader([
            'typ' => TokenVersion::QT1,
            'enc' => EncryptionType::AES256,
            'iss' => 1534723200000,
            'ti' => 'a21d423a-43fd-4821-84fa-4390f6a2fd3e',
            'c' => SampleTokenValues::$customerId,
        ]);

        return self::sign($header . "." . Base64UrlEncoding::encode("not a cipher block"));
    }
}
