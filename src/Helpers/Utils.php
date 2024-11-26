<?php

namespace QueueIT\Helpers;

use DateTime;
use DateTimeZone;

class Utils
{

    public static function maxDate()
    {
        $maxUtcDate = new DateTime('9999-12-31 23:59:59.999', new DateTimeZone('UTC'));
        return $maxUtcDate->getTimestamp() * 1000;
    }

    public static function utcNow()
    {
        $utcNow = new DateTime('now', new DateTimeZone('UTC'));
        return $utcNow->getTimestamp() * 1000;
    }

    public static function padRight($str, $padding, $stringSize)
    {
        while (strlen($str) < $stringSize) {
            $str .= $padding;
        }
        return $str;
    }

    // Based on REF 4122 section 4.4 http://www.ietf.org/rfc/rfc4122.txt
    public static function generateUUID()
    {
        $s = [];
        $hexDigits = "0123456789abcdef";
        for ($i = 0; $i < 36; $i++) {
            $s[$i] = substr($hexDigits, rand(0, 15), 1);
        }
        $s[14] = "4";  // bits 12-15 of the time_hi_and_version field to 0010
        $s[19] = substr($hexDigits, (hexdec($s[19]) & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
        $s[8] = $s[13] = $s[18] = $s[23] = "-";

        return implode("", $s);
    }

    public static function uint8ArrayToHexString($byteArray)
    {
        $acc = '';
        foreach ($byteArray as $val) {
            $acc .= str_pad(dechex($val), 2, '0', STR_PAD_LEFT);
        }
        return $acc;
    }

    public static function uint8ArrayToString($array)
    {
        $out = '';
        $out = implode(array_map('chr', $array));
        return $out;
    }

    public static function stringToUint8Array($value)
    {
        $encoded = urlencode($value);
        return array_map('ord', str_split($encoded));
    }

}