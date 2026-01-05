<?php

namespace QueueIT\QueueToken;

use DateTime;
use Exception;

use QueueIT\QueueToken\Exceptions\ArgumentException;
use QueueIT\QueueToken\Exceptions\InvalidHashException;
use QueueIT\QueueToken\Exceptions\TokenDeserializationException;
use QueueIT\QueueToken\Exceptions\TokenSerializationException;
use QueueIT\QueueToken\Helpers\Base64UrlEncoding;
use QueueIT\QueueToken\Helpers\ShaHashing;
use QueueIT\QueueToken\Helpers\Utils;
use QueueIT\QueueToken\Models\HeaderDto;
use QueueIT\QueueToken\Models\EncryptionType;
use QueueIT\QueueToken\Models\TokenVersion;

class EnqueueToken implements IEnqueueToken
{
    private ?string $tokenIdentifierPrefix;
    private string $tokenIdentifier;
    private ?IEnqueueTokenPayload $payload;
    private ?string $tokenWithoutHash;
    private ?string $hashCode;

    public string $customerId;
    public ?string $eventId;
    public ?string $ipAddress;
    public ?string $xForwardedFor;
    public DateTime $issued;
    public string $tokenVersion = TokenVersion::QT1;
    public string $encryption = EncryptionType::AES256;
    public DateTime $expires;

    public function __construct(string $customerId, ?string $tokenIdentifierPrefix = "")
    {
        $this->tokenIdentifierPrefix = $tokenIdentifierPrefix ?? "";
        $this->customerId = $customerId;
        $this->eventId = null;
        $this->ipAddress = null;
        $this->xForwardedFor = null;
        $this->issued = Utils::utcNow();
        $this->expires = Utils::maxDate();
        $this->tokenIdentifier = $this->generateTokenIdentifier($this->tokenIdentifierPrefix);
        $this->payload = null;
        $this->tokenWithoutHash = null;
        $this->hashCode = null;
    }

    public function getPayload(): ?IEnqueueTokenPayload
    {
        return $this->payload;
    }

    public function setPayload(?IEnqueueTokenPayload $payload): void
    {
        $this->payload = $payload;
    }

    public function getToken(): string
    {
        return $this->getTokenWithoutHash() . "." . $this->getHashCode();
    }

    public function getHashCode(): ?string
    {
        return $this->hashCode;
    }

    public function setHashCode(?string $hashCode): void
    {
        $this->hashCode = $hashCode;
    }

    public function getTokenWithoutHash(): ?string
    {
        return $this->tokenWithoutHash;
    }

    public function setTokenWithoutHash(?string $value): void
    {
        $this->tokenWithoutHash = $value;
    }

    public function getTokenIdentifier(): string
    {
        return $this->tokenIdentifier;
    }

    public function setTokenIdentifier(string $value): void
    {
        $this->tokenIdentifier = $value;
    }

    public static function create(
        string $tokenIdentifier,
        string $customerId,
        ?string $eventId,
        DateTime $issued,
        ?DateTime $expires,
        ?string $ipAddress,
        ?string $xForwardedFor,
        ?IEnqueueTokenPayload $payload
    ): EnqueueToken {
        $token = new EnqueueToken($customerId, "");
        $token->tokenIdentifier = $tokenIdentifier;
        $token->customerId = $customerId;
        $token->eventId = $eventId;
        $token->issued = $issued;
        $token->expires = $expires ?? Utils::maxDate();
        $token->payload = $payload;
        $token->ipAddress = $ipAddress;
        $token->xForwardedFor = $xForwardedFor;
        return $token;
    }

    private function generateTokenIdentifier(?string $tokenIdentifierPrefix): string
    {
        $prefix = $tokenIdentifierPrefix ?? "";
        return !empty($prefix)
            ? $prefix . '~' . Utils::generateUUID()
            : Utils::generateUUID();
    }

    public function generate(string $secretKey, bool $resetTokenIdentifier = true): void
    {
        if ($resetTokenIdentifier) {
            $this->setTokenIdentifier($this->generateTokenIdentifier($this->tokenIdentifierPrefix));
        }

        try {
            $dto = $this->createHeaderDto();
            $serialized = $dto->serialize() . ".";

            if ($this->getPayload()) {
                $serialized .= $this->getPayload()->encryptAndEncode($secretKey, $this->getTokenIdentifier());
            }

            $this->tokenWithoutHash = $serialized;
            $sha256Hash = ShaHashing::generateHash($secretKey, $this->getTokenWithoutHash());
            $hashString = Base64UrlEncoding::encode($sha256Hash);
            $this->setHashCode($hashString);
        } catch (Exception $ex) {
            throw new TokenSerializationException($ex);
        }
    }

    private function createHeaderDto(): HeaderDto
    {
        $dto = new HeaderDto();
        $dto->customerId = $this->customerId;
        $dto->eventId = $this->eventId;
        $dto->tokenIdentifier = $this->getTokenIdentifier();
        $dto->issued = $this->issued->getTimestamp() * 1000;
        $dto->expires = $this->expires->getTimestamp() * 1000;
        $dto->encryption = EncryptionType::AES256;
        $dto->tokenVersion = TokenVersion::QT1;
        $dto->ipAddress = $this->ipAddress;
        $dto->xForwardedFor = $this->xForwardedFor;
        return $dto;
    }

    public static function parse(string $tokenString, string $secretKey): IEnqueueToken
    {
        self::validateParseInputs($tokenString, $secretKey);

        $tokenParts = explode(".", $tokenString);
        $headerPart = $tokenParts[0];
        $payloadPart = $tokenParts[1] ?? '';
        $hashPart = $tokenParts[2];

        if (empty($headerPart) || empty($hashPart)) {
            throw new ArgumentException("Invalid token");
        }

        $token = $headerPart . "." . $payloadPart;
        self::validateTokenHash($token, $hashPart, $secretKey);

        try {
            return self::deserializeToken($headerPart, $payloadPart, $token, $hashPart, $secretKey);
        } catch (Exception $ex) {
            throw new TokenDeserializationException("Unable to deserialize token", $ex);
        }
    }

    private static function validateParseInputs(string $tokenString, string $secretKey): void
    {
        if (empty($secretKey)) {
            throw new ArgumentException("Invalid secret key");
        }
        if (empty($tokenString)) {
            throw new ArgumentException("Invalid token");
        }
    }

    private static function validateTokenHash(string $token, string $hashPart, string $secretKey): void
    {
        $hash = ShaHashing::generateHash($secretKey, $token);
        $expectedHash = Base64UrlEncoding::encode($hash);
        if ($expectedHash !== $hashPart) {
            throw new InvalidHashException();
        }
    }

    private static function deserializeToken(string $headerPart, string $payloadPart, string $token, string $hashPart, string $secretKey): IEnqueueToken
    {
        $headerModel = HeaderDto::deserializeHeader($headerPart);
        $payload = !empty($payloadPart)
            ? EnqueueTokenPayload::deserialize($payloadPart, $secretKey, $headerModel->tokenIdentifier)
            : null;

        $issuedTime = new DateTime('@' . ($headerModel->issued / 1000));
        $expiresDate = $headerModel->expires ? new DateTime('@' . ($headerModel->expires / 1000)) : null;

        $enqueueToken = EnqueueToken::create(
            $headerModel->tokenIdentifier,
            $headerModel->customerId,
            $headerModel->eventId,
            $issuedTime,
            $expiresDate,
            $headerModel->ipAddress,
            $headerModel->xForwardedFor,
            $payload
        );

        $enqueueToken->setTokenWithoutHash($token);
        $enqueueToken->setHashCode($hashPart);
        return $enqueueToken;
    }
}
