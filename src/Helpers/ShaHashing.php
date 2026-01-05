<?php

namespace QueueIT\QueueToken\Helpers;

class ShaHashing
{
    public static function generateHash(string $secretKey, string $tokenString): string
    {
        $combinedString = $tokenString . $secretKey;
        return  hash('sha256', $combinedString, true);
    }
}
