<?php

namespace QueueIT\QueueToken\Helpers;

class Base64UrlEncoding
{
    public static function encode(string $input): string
    {
        $base64 = base64_encode($input);

        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

    public static function decode(string $input): string
    {
        $base64 = str_replace(['-', '_'], ['+', '/'], $input);

        // Add padding if necessary
        $mod = strlen($base64) % 4;
        if ($mod > 0) {
            $base64 .= str_repeat('=', 4 - $mod);
        }

        return base64_decode($base64);
    }
}
