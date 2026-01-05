<?php

namespace QueueIT\QueueToken\Models;

use QueueIT\QueueToken\Helpers\Base64UrlEncoding;

class HeaderDto
{
    public ?string $tokenVersion;
    public ?string $encryption;
    public ?int $issued; // Epoch Time in milliseconds
    public ?int $expires; // Epoch Time in milliseconds
    public ?string $tokenIdentifier;
    public ?string $customerId;
    public ?string $eventId;
    public ?string $ipAddress;
    public ?string $xForwardedFor;

    public static function deserializeHeader(string $input): HeaderDto
    {
        $decoded = Base64UrlEncoding::decode($input);
        $jsonData = json_decode($decoded, true);

        // Validate required fields exist
        if (
            !isset($jsonData['typ']) || !isset($jsonData['enc']) || !isset($jsonData['iss'])
            || !isset($jsonData['ti']) || !isset($jsonData['c'])
        ) {
            throw new \QueueIT\QueueToken\Exceptions\ArgumentException("Invalid token header: missing required fields");
        }

        $header = new HeaderDto();
        // Required fields - must be present
        $header->tokenVersion = $jsonData['typ'];
        $header->encryption = $jsonData['enc'];
        $header->issued = $jsonData['iss'];
        $header->tokenIdentifier = $jsonData['ti'];
        $header->customerId = $jsonData['c'];

        // Optional fields - use null coalescing
        $header->expires = $jsonData['exp'] ?? null;
        $header->eventId = $jsonData['e'] ?? null;
        $header->ipAddress = $jsonData['ip'] ?? null;
        $header->xForwardedFor = $jsonData['xff'] ?? null;

        return $header;
    }

    public function serialize(): string
    {
        $obj = [
            'typ' => $this->tokenVersion,
            'enc' => $this->encryption,
            'iss' => $this->issued,
        ];

        if ($this->expires !== null) {
            $obj['exp'] = $this->expires;
        }
        $obj['ti'] = $this->tokenIdentifier;
        $obj['c'] = $this->customerId;

        if ($this->eventId !== null) {
            $obj['e'] = $this->eventId;
        }
        if ($this->ipAddress !== null) {
            $obj['ip'] = $this->ipAddress;
        }
        if ($this->xForwardedFor !== null) {
            $obj['xff'] = $this->xForwardedFor;
        }

        $jsonData = json_encode($obj);
        return Base64UrlEncoding::encode($jsonData);
    }
}
