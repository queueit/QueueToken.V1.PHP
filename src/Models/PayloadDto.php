<?php
namespace QueueIT\QueueToken\Models;

require_once __DIR__.'/TokenOrigin.php';
require_once __DIR__.'/../Helpers/Utils.php';
require_once __DIR__.'/../Helpers/Base64UrlEncoding.php';

use QueueIT\Helpers\Utils;
use QueueIT\Helpers\Base64UrlEncoding;
use Queueit\Helpers\AESEncryption;


class PayloadDto {
    public ?float $RelativeQuality;
    public ?string $Key;
    public ?array $CustomData=[];
    public ?string $Origin;

    public function Serialize(): array
    {
        $obj = [
            'r' => $this->RelativeQuality,
            'k' => $this->Key??"",
        ];

        if (!empty($this->CustomData)) {
            $obj['cd'] = $this->CustomData;
        }

        if ($this->Origin) {
            $obj['o'] = $this->Origin;
        }

        $jsonString = json_encode($obj);
        // Convert JSON string to byte array
        return unpack('C*', $jsonString);

    }

    public static function DeserializePayload($input, $secretKey, $tokenIdentifier): ?PayloadDto {
        $headerEncrypted = Base64UrlEncoding::decode($input); // Decode the input
        $decryptedBytes = AESEncryption::DecryptPayload($secretKey, $tokenIdentifier, $headerEncrypted); // Decrypt the payload
        $jsonData = json_decode(Utils::uint8ArrayToString($decryptedBytes), true); // Decode JSON

        if ($jsonData === null) {
            return null; // Return null if JSON decoding fails
        }

        $payload = new PayloadDto();
        $payload->RelativeQuality = $jsonData['r'] ?? null;
        $payload->Key = $jsonData['k'];

        if (isset($jsonData['cd'])) {
            $payload->CustomData = $jsonData['cd'];
        }

        if (isset($jsonData['o'])) {
            $payload->Origin = $jsonData['o'];
        }

        return $payload;
    }
}
