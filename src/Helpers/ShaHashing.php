<?php

namespace QueueIT\Helpers;
class ShaHashing
{
    public static function GenerateHash($secretKey, $tokenString)
    {
        $combinedString = $tokenString . $secretKey;
        $content = hash('sha256', $combinedString, true);
        return $content;
    }
}