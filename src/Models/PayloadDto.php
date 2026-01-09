<?php

namespace QueueIT\QueueToken\Models;

use QueueIT\QueueToken\Helpers\Utils;
use QueueIT\QueueToken\Helpers\Base64UrlEncoding;
use QueueIT\QueueToken\Helpers\AESEncryption;

class PayloadDto
{
    public ?float $relativeQuality;
    public ?string $key;
    public ?array $customData = [];
    public ?string $origin;

    public function serialize(): array
    {
        $obj = [
            'r' => $this->relativeQuality,
            'k' => $this->key ?? null,
        ];

        if (!empty($this->customData)) {
            $obj['cd'] = $this->customData;
        }

        if ($this->origin) {
            $obj['o'] = $this->origin;
        }

        $jsonString = json_encode($obj);
        return array_values(unpack('C*', $jsonString));
    }

    public static function deserializePayload(string $input, string $secretKey, string $tokenIdentifier): ?PayloadDto
    {
        $headerEncrypted = Base64UrlEncoding::decode($input);
        $decryptedBytes = AESEncryption::decryptPayload($secretKey, $tokenIdentifier, $headerEncrypted);
        $jsonData = json_decode(Utils::uint8ArrayToString($decryptedBytes), true);

        if ($jsonData === null) {
            return null;
        }

        $payload = new PayloadDto();
        $payload->relativeQuality = $jsonData['r'] ?? null;
        $payload->key = $jsonData['k'] ?? null;
        $payload->customData = $jsonData['cd'] ?? [];
        $payload->origin = $jsonData['o'] ?? null;

        return $payload;
    }
}
