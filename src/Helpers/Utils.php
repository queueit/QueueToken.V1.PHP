<?php

namespace QueueIT\QueueToken\Helpers;

use DateTime;
use DateTimeZone;

class Utils
{

    public static function maxDate(): DateTime
    {
        return new DateTime('9999-12-31 23:59:59.999', new DateTimeZone('UTC'));
    }

    public static function utcNow(): DateTime
    {
        return new DateTime('now', new DateTimeZone('UTC'));
    }

    public static function padRight(string $str, string $padding, int $stringSize): string
    {
        return str_pad($str, $stringSize, $padding, STR_PAD_RIGHT);
    }

    public static function generateUUID(): string
    {
        // Use PHP's built-in random_bytes for cryptographically secure UUID v4
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function uint8ArrayToHexString(array $byteArray): string
    {
        return bin2hex(implode(array_map('chr', $byteArray)));
    }

    public static function uint8ArrayToString(array $array): string
    {
        return implode(array_map('chr', $array));
    }

    public static function stringToUint8Array(string $value): array
    {
        $encoded = urlencode($value);
        return array_map('ord', str_split($encoded));
    }
}
