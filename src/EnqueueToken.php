<?php

namespace QueueIT\QueueToken;

require 'IEnqueueToken.php';

use DateTime;
use DateTimeZone;
use QueueIT\Exceptions\TokenDeserializationException;
use QueueIT\Helpers\Base64UrlEncoding;
use QueueIT\Helpers\ShaHashing;
use QueueIT\Helpers\Utils;
use QueueIT\QueueToken\Models\HeaderDto;


class EnqueueToken implements IEnqueueToken
{
    private ?string $_tokenIdentifierPrefix;
    public string $CustomerId;
    public $EventId;
    public $IpAddress;
    public $XForwardedFor;
    public DateTime $Issued;
    public string $TokenVersion = 'QT1'; // Use appropriate constant for TokenVersion
    public string $Encryption = 'AES256'; // Use appropriate constant for EncryptionType
    public DateTime $Expires;
    private string $_tokenIdentifier;
    private $_payload;
    private $_tokenWithoutHash;
    private $_hashCode;

    public function __construct($customerId, $tokenIdentifierPrefix = "")
    {
        $this->_tokenIdentifierPrefix = $tokenIdentifierPrefix;
        $this->CustomerId = $customerId;
        $this->Issued = new DateTime('now', new DateTimeZone('UTC'));//->getTimestamp() * 1000;
        $this->Expires = (new DateTime())->setTimestamp(Utils::maxDate() / 1000);//->getTimestamp() * 1000;
        $this->_tokenIdentifier = $this->GetTokenIdentifier($tokenIdentifierPrefix);
    }

    public function getPayload()
    {
        return $this->_payload;
    }

    public function setPayload($value)
    {
        $this->_payload = $value;
    }

    public function getToken()
    {
        return $this->getTokenWithoutHash() . "." . $this->getHashCode();
    }

    public function getHashCode()
    {
        return $this->_hashCode;
    }

    public function setHashCode($value)
    {
        $this->_hashCode = $value;
    }

    public function getTokenWithoutHash()
    {
        return $this->_tokenWithoutHash;
    }

    public function setTokenWithoutHash($value)
    {
        $this->_tokenWithoutHash = $value;
    }

    public function Issued()
    {
        return $this->_issued;
    }

    public function TokenIdentifier()
    {
        return $this->_tokenIdentifier;
    }

    public function setTokenIdentifier($value)
    {
        $this->_tokenIdentifier = $value;
    }

    public static function Create($tokenIdentifier, $customerId, $eventId, $issued, $expires, $ipAddress, $xForwardedFor, $payload)
    {
        $token = new EnqueueToken($customerId, "");
        $token->setTokenIdentifier($tokenIdentifier);
        $token->CustomerId = $customerId;
        $token->EventId = $eventId;
        $token->Issued = $issued;
        $token->Expires = $expires ?? new DateTime('9999-12-31 23:59:59');
        $token->setPayload($payload);
        $token->IpAddress = $ipAddress;
        $token->XForwardedFor = $xForwardedFor;
        return $token;
    }

    private static function GetTokenIdentifier($tokenIdentifierPrefix)
    {
        return !empty($tokenIdentifierPrefix) ? $tokenIdentifierPrefix . '~' . Utils::generateUUID() : Utils::generateUUID();
    }

    public function Generate($secretKey, $resetTokenIdentifier = true)
    {
        if ($resetTokenIdentifier) {
            $this->setTokenIdentifier(self::GetTokenIdentifier($this->_tokenIdentifierPrefix));
        }

        try {
            $dto = new HeaderDto();
            $dto->CustomerId = $this->CustomerId;
            $dto->EventId = $this->EventId;
            $dto->TokenIdentifier = $this->TokenIdentifier();
            $dto->Issued = $this->Issued->getTimestamp() * 1000;//$utcTimeIssued to epochtime;
            $dto->Expires = $this->Expires->getTimestamp() * 1000;//$utcTimeExpires to epochtime;
            $dto->Encryption = 'AES256'; // Set appropriately
            $dto->TokenVersion = 'QT1'; // Set appropriately
            $dto->IpAddress = $this->IpAddress;
            $dto->XForwardedFor = $this->XForwardedFor;

            $serialized = $dto->Serialize() . ".";
            if ($this->getPayload()) {
                $serialized .= $this->getPayload()->EncryptAndEncode($secretKey, $this->TokenIdentifier());
            }
            $this->_tokenWithoutHash = $serialized;
            $sha256Hash = ShaHashing::GenerateHash($secretKey, $this->getTokenWithoutHash());
            $hashString = Base64UrlEncoding::Encode($sha256Hash);

            $this->setHashCode($hashString);
        } catch (Exception $ex) {
            throw new TokenSerializationException($ex);
        }
    }

    public static function Parse($tokenString, $secretKey): IEnqueueToken
    {
        if (empty($secretKey)) {
            throw new ArgumentException("Invalid secret key");
        }
        if (empty($tokenString)) {
            throw new ArgumentException("Invalid token");
        }

        $tokenParts = explode(".", $tokenString);
        $headerPart = $tokenParts[0];
        $payloadPart = $tokenParts[1];
        $hashPart = $tokenParts[2];

        if (empty($headerPart) || empty($hashPart)) {
            throw new ArgumentException("Invalid token");
        }

        $token = $headerPart . "." . $payloadPart;
        $hash = ShaHashing::GenerateHash($secretKey, $token);
        $expectedHash = Base64UrlEncoding::Encode($hash);
        if ($expectedHash !== $hashPart) {
            throw new InvalidHashException();
        }

        try {
            $headerModel = HeaderDto::DeserializeHeader($headerPart);
            $payload = !empty($payloadPart) ? EnqueueTokenPayload::Deserialize($payloadPart, $secretKey, $headerModel->TokenIdentifier) : null;

            $issuedTime = new DateTime('@' . ($headerModel->Issued / 1000));
            $expiresDate = $headerModel->Expires ? new DateTime('@' . ($headerModel->Expires / 1000)) : null;
            $enqueueToken = EnqueueToken::Create(
                $headerModel->TokenIdentifier,
                $headerModel->CustomerId,
                $headerModel->EventId,
                $issuedTime,
                $expiresDate,
                $headerModel->IpAddress,
                $headerModel->XForwardedFor,
                $payload
            );
            $enqueueToken->setTokenWithoutHash($token);
            $enqueueToken->setHashCode($expectedHash);
            return $enqueueToken;
        } catch (Exception $ex) {
            throw new TokenDeserializationException("Unable to deserialize token", $ex);
        }
    }

    public static function AddExpires($token, $newExpiryTime)
    {
        $token->Expires = clone $newExpiryTime;
        return $token;
    }

    public static function AddEventId($token, $eventId)
    {
        $token->EventId = $eventId;
        return $token;
    }

    public static function AddExpiresWithDate($token, $validity)
    {
        $token->Expires = $validity;
        return $token;
    }

    public static function AddPayload($token, $payload)
    {
        $token->setPayload($payload);
        return $token;
    }

    public static function AddIPAddress($token, $ip, $xForwardedFor)
    {
        $token->IpAddress = $ip;
        $token->XForwardedFor = $xForwardedFor;
        return $token;
    }
}