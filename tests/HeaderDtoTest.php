<?php

namespace QueueIT\QueueToken\Tests;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Models\HeaderDto;
use QueueIT\QueueToken\Helpers\Base64UrlEncoding;

class HeaderDtoTest extends TestCase
{
    public function testDeserializeHeaderAllowsNullOptionalFields()
    {
        $data = [
            'typ' => 'v1',
            'enc' => 'AES',
            'iss' => 1234567890,
            'ti' => 'tokenid',
            'c' => 'customerid'
            // No exp, e, ip, xff
        ];
        $json = json_encode($data);
        $encoded = Base64UrlEncoding::encode($json);
        $header = HeaderDto::deserializeHeader($encoded);

        $this->assertEquals('v1', $header->tokenVersion);
        $this->assertEquals('AES', $header->encryption);
        $this->assertEquals(1234567890, $header->issued);
        $this->assertEquals('tokenid', $header->tokenIdentifier);
        $this->assertEquals('customerid', $header->customerId);
        $this->assertNull($header->expires);
        $this->assertNull($header->eventId);
        $this->assertNull($header->ipAddress);
        $this->assertNull($header->xForwardedFor);
    }

    public function testDeserializeHeaderThrowsOnMissingRequiredFields()
    {
        $data = [
            'typ' => 'v1',
            // Missing enc, iss, ti, c
        ];
        $json = json_encode($data);
        $encoded = Base64UrlEncoding::encode($json);
        $this->expectException(\QueueIT\QueueToken\Exceptions\ArgumentException::class);
        HeaderDto::deserializeHeader($encoded);
    }
}
