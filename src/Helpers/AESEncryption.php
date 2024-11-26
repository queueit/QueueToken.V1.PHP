<?php
namespace QueueIT\Helpers;

require 'ShaHashing.php';

use QueueIT\Helpers\ShaHashing;
class AESEncryption {

    public static function EncryptPayload($secretKey, $tokenIdentifier, $valueToEncrypt) {
        $key = ShaHashing::GenerateHash($secretKey, '');
        $iv = md5($tokenIdentifier, true);
        $encryptedData = openssl_encrypt(pack('C*', ...$valueToEncrypt), 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return $encryptedData;
    }


    public static function DecryptPayload($secretKey, $tokenIdentifier, $encryptedData) {
        // Derive the key using the same hashing method as EncryptPayload
        $key = ShaHashing::GenerateHash($secretKey, '');

        // Generate the IV using the token identifier
        $iv = md5($tokenIdentifier, true);
        // Decrypt the data
        $decryptedData = openssl_decrypt($encryptedData, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($decryptedData === false) {
            throw new Exception("Decryption failed.");
        }
        // Convert decrypted binary data back to an array of integers
        $decryptedArray = array_values(unpack('C*', $decryptedData));

        return $decryptedArray;
    }
}

