<?php

namespace QueueIT\QueueToken;

use Exception;

use QueueIT\QueueToken\Exceptions\TokenSerializationException;
use QueueIT\QueueToken\Helpers\AESEncryption;
use QueueIT\QueueToken\Helpers\Base64UrlEncoding;
use QueueIT\QueueToken\Models\PayloadDto;
use QueueIT\QueueToken\Models\TokenOrigin;

class EnqueueTokenPayload implements IEnqueueTokenPayload
{
    private array $customData;
    private ?string $key;
    private ?float $relativeQuality;
    private string $origin;

    public function __construct(
        ?string $key = null,
        ?float $relativeQuality = null,
        array $customData = [],
        string $origin = TokenOrigin::CONNECTOR
    ) {
        $this->key = $key;
        $this->relativeQuality = $relativeQuality;
        $this->customData = $customData;
        $this->origin = $origin;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setKey(?string $value): EnqueueTokenPayload
    {
        $this->key = $value;
        return $this;
    }

    public function getCustomData(): array
    {
        return $this->customData;
    }

    public function setCustomData(array $customData): EnqueueTokenPayload
    {
        $this->customData = $customData;
        return $this;
    }

    public function getRelativeQuality(): ?float
    {
        return $this->relativeQuality;
    }

    public function setRelativeQuality(?float $value): EnqueueTokenPayload
    {
        $this->relativeQuality = $value;
        return $this;
    }

    public function getTokenOrigin(): string
    {
        return $this->origin;
    }

    public function setTokenOrigin(string $origin): EnqueueTokenPayload
    {
        $this->origin = $origin;
        return $this;
    }

    public function addCustomData(string $key, $value): EnqueueTokenPayload
    {
        $this->customData[$key] = $value;
        return $this;
    }

    public function serialize(): array
    {
        $dto = new PayloadDto();
        $dto->key = $this->getKey();
        $dto->relativeQuality = $this->getRelativeQuality();
        $dto->customData = $this->getCustomData();
        $dto->origin = $this->getTokenOrigin();

        return $dto->serialize();
    }

    public static function deserialize(string $input, string $secretKey, string $tokenIdentifier): ?EnqueueTokenPayload
    {
        $dto = PayloadDto::deserializePayload($input, $secretKey, $tokenIdentifier);
        if ($dto === null) {
            return null;
        }

        return new EnqueueTokenPayload(
            $dto->key,
            $dto->relativeQuality,
            $dto->customData ?? [],
            $dto->origin ?? TokenOrigin::CONNECTOR
        );
    }

    public function encryptAndEncode(string $secretKey, string $tokenIdentifier): string
    {
        try {
            $serializedPayload = $this->serialize();
            $encrypted = AESEncryption::encryptPayload($secretKey, $tokenIdentifier, $serializedPayload);
            return Base64UrlEncoding::encode($encrypted);
        } catch (Exception $ex) {
            throw new TokenSerializationException($ex);
        }
    }
}
