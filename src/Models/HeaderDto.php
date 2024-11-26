<?php

namespace QueueIT\QueueToken\Models;

use QueueIT\Helpers\Base64UrlEncoding;
class HeaderDto {
    public $TokenVersion;
    public $Encryption;
    public $Issued;  //Epoch Time in milliseconds
    public $Expires; //Epoch Time in milliseconds
    public $TokenIdentifier;
    public $CustomerId;
    public $EventId;
    public $IpAddress;
    public $XForwardedFor;

    public static function DeserializeHeader($input) {
        $decoded = Base64UrlEncoding::Decode($input); // Assume Base64::decode works similarly to the TypeScript version
        $jsonData = json_decode($decoded, true);
        $header = new HeaderDto();
        $header->TokenVersion = $jsonData['typ'];
        $header->Encryption = $jsonData['enc'];
        $header->Issued = $jsonData['iss'];
        $header->Expires = $jsonData['exp'] ?? null;
        $header->TokenIdentifier = $jsonData['ti'];
        $header->CustomerId = $jsonData['c'];
        $header->EventId = $jsonData['e'];
        $header->IpAddress = $jsonData['ip'];
        $header->XForwardedFor = $jsonData['xff'];

        return $header;
    }

    public function Serialize(): string {
        $obj = [
            'typ' => $this->TokenVersion,
            'enc' => $this->Encryption,
            'iss' => $this->Issued,
        ];
        
        if ($this->Expires !== null) {
            $obj['exp'] = $this->Expires;
        }
        $obj['ti'] = $this->TokenIdentifier;
        $obj['c'] = $this->CustomerId;

        if ($this->EventId !== null) {
            $obj['e'] = $this->EventId;
        }
        if ($this->IpAddress !== null) {
            $obj['ip'] = $this->IpAddress;
        }
        if ($this->XForwardedFor !== null) {
            $obj['xff'] = $this->XForwardedFor;
        }

        $jsonData = json_encode($obj);
        return Base64UrlEncoding::Encode($jsonData);
    }
}
