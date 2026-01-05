<?php

namespace QueueIT\QueueToken\Tests;

use DateTime;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Token;
use QueueIT\QueueToken\EnqueueToken;
use QueueIT\QueueToken\Payload;
use QueueIT\QueueToken\Helpers\Utils;
use QueueIT\QueueToken\Models\TokenVersion;
use QueueIT\QueueToken\Models\EncryptionType;
use QueueIT\QueueToken\Models\HeaderDto;

use QueueIT\QueueToken\Tests\SampleTokenValues;

class EnqueueTokenTest extends TestCase
{
    public function testCreateSimpleToken()
    {
        $startTime = Utils::utcNow();
        $expectedCustomerId = SampleTokenValues::$customerId;
        $token = Token::enqueue($expectedCustomerId)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertEquals($expectedCustomerId, $token->customerId);
        $this->assertNotNull($token->getTokenIdentifier());
        $this->assertEquals(TokenVersion::QT1, $token->tokenVersion);
        $this->assertEquals(EncryptionType::AES256, $token->encryption);
        $this->assertTrue($startTime <= $token->issued);
        $this->assertTrue(Utils::utcNow() >= $token->issued);
        $this->assertEquals(Utils::maxDate(), $token->expires);
        $this->assertNull($token->eventId);
        // $this->assertNull($token->Payload);
    }

    public function testCreateTokenWithIdentifierPrefix()
    {
        $tokenIdentifierPrefix = "SomePrefix";

        $token = Token::enqueue(SampleTokenValues::$customerId, $tokenIdentifierPrefix)
            ->generate(SampleTokenValues::$secretKey);

        $tokenIdentifierParts = explode("~", $token->getTokenIdentifier());
        $this->assertEquals($tokenIdentifierPrefix, $tokenIdentifierParts[0]);
    }

    public function testCreateTokenWithValidityAsLong()
    {
        $expectedValidity = 3000;

        $token = Token::enqueue(SampleTokenValues::$customerId)
            ->withValidity($expectedValidity)
            ->generate(SampleTokenValues::$secretKey);
        $intervalInSeconds = $expectedValidity / 1000;
        $expectedExpiry = $token->issued->modify("+{$intervalInSeconds} seconds");
        $this->assertEquals($expectedExpiry->getTimestamp() * 1000, $token->expires->getTimestamp() * 1000);
    }

    public function testCreateTokenWithValidityAsDate()
    {
        $expectedValidity = new DateTime("2030-02-01 12:00:00");

        $token = Token::enqueue(SampleTokenValues::$customerId)
            ->withValidityDate($expectedValidity)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertEquals($expectedValidity->getTimestamp(), $token->expires->getTimestamp());
    }

    public function testCreateTokenWithEventId()
    {
        $expectedEventId = SampleTokenValues::$eventId;

        $token = Token::enqueue(SampleTokenValues::$customerId)
            ->withEventId($expectedEventId)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertEquals($expectedEventId, $token->eventId);
    }

    public function testCreateTokenWithIpAddress()
    {
        $expectedIpAddress = "1.5.8.9";
        $expectedXForwardedFor = "45.67.2.4,34.56.3.2";

        $token = Token::enqueue(SampleTokenValues::$customerId)
            ->withIpAddress($expectedIpAddress, $expectedXForwardedFor)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertEquals($expectedIpAddress, $token->ipAddress);
        $this->assertEquals($expectedXForwardedFor, $token->xForwardedFor);
    }

    public function testCreateTokenWithPayload()
    {
        $expectedPayload = Payload::enqueue()->withKey(SampleTokenValues::$customDataKey)->generate();

        $token = Token::enqueue(SampleTokenValues::$customerId)
            ->withPayload($expectedPayload)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertEquals($expectedPayload, $token->getPayload());
    }

    public function testCreateTokenWithPayloadKeyAndRelativeQuality()
    {
        $expectedEventId = SampleTokenValues::$eventId;
        $expectedCustomerId = SampleTokenValues::$customerId;
        $expectedValidity = 3000;

        $expectedPayload = Payload::enqueue()->withKey(SampleTokenValues::$customDataKey)->generate();

        $token = Token::enqueue($expectedCustomerId)
            ->withPayload($expectedPayload)
            ->withEventId($expectedEventId)
            ->withValidity($expectedValidity)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertEquals($expectedCustomerId, $token->customerId);
        $this->assertEquals($expectedEventId, $token->eventId);
        $this->assertEquals($expectedValidity, $token->expires->getTimestamp() * 1000 - $token->issued->getTimestamp() * 1000);
        $this->assertEquals($expectedPayload, $token->getPayload());
    }

    // TODO: Use this to test the hashing algorithm
    public function testSignTokenWithoutPayload()
    {
        $expectedSignedToken = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MTUzOTEyOTYwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50IiwiaXAiOiI1LjcuOC42IiwieGZmIjoiNDUuNjcuMi40LDM0LjU2LjMuMiJ9..wUOdVDIKlrIqumpU33bShDPdvTkicRk3q4Z-Vs8epFc";


        $token = EnqueueToken::create(
            "a21d423a-43fd-4821-84fa-4390f6a2fd3e",
            SampleTokenValues::$customerId,
            SampleTokenValues::$eventId,
            new DateTime("2018-08-20T00:00:00Z"), // TODO: be sure the values for the test are correct.
            new DateTime("2018-10-10T00:00:00Z"),
            "5.7.8.6",
            "45.67.2.4,34.56.3.2",
            null
        );

        $token->generate(SampleTokenValues::$secretKey, false);

        $actualSignedToken = $token->getToken();

        $this->assertEquals($expectedSignedToken, $actualSignedToken);
    }


    public function testSignTokenWithPayloadAndCustomData()
    {
        $expectedSignedToken = SampleTokenValues::$testToken;

        $payload = Payload::enqueue()
            ->withKey(SampleTokenValues::$customDataKey)
            ->withRelativeQuality(SampleTokenValues::$relativeQuality)
            ->withCustomData("color", SampleTokenValues::$customData["color"])
            ->withCustomData("size", SampleTokenValues::$customData["size"])
            ->generate();

        $token = EnqueueToken::create(
            "a21d423a-43fd-4821-84fa-4390f6a2fd3e",
            SampleTokenValues::$customerId,
            SampleTokenValues::$eventId,
            new DateTime("2018-08-20T00:00:00Z"),
            new DateTime("2018-10-10T00:00:00Z"),
            null,
            null,
            $payload
        );

        $token->generate(SampleTokenValues::$secretKey, false);

        $actualSignedToken = $token->getToken();

        $this->assertEquals($expectedSignedToken, $actualSignedToken);
    }

    public function testSerializeHeaders()  // Successful JR. 11/4/24
    {
        $expectedText = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MTUzOTEyOTYwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50IiwiaXAiOiI1LjcuOC42IiwieGZmIjoiNDUuNjcuMi40LDM0LjU2LjMuMiJ9.";
        $dto = new HeaderDto();
        $dto->customerId = SampleTokenValues::$customerId;
        $dto->eventId = SampleTokenValues::$eventId;
        $dto->tokenIdentifier = "a21d423a-43fd-4821-84fa-4390f6a2fd3e";
        // $dto->Issued = new DateTime("2018-08-20T00:00:00Z");
        $issuedDateTime = new DateTime("2018-08-20T00:00:00Z");
        $issuedDateTimeMicro = $issuedDateTime->getTimestamp() * 1000;
        $dto->issued = $issuedDateTimeMicro;
        $expiresTime = new DateTime("2018-10-10T00:00:00Z");
        $expTimeMicro = $expiresTime->getTimestamp() * 1000;
        $dto->expires = $expTimeMicro;
        // $dto->Issuer = "myIssuer";
        $dto->encryption = EncryptionType::AES256;
        $dto->tokenVersion = TokenVersion::QT1;
        $dto->ipAddress = "5.7.8.6";
        $dto->xForwardedFor = "45.67.2.4,34.56.3.2";
        $actualText = $dto->serialize() . ".";

        $this->assertEquals($expectedText, $actualText);
    }

    public function testTokenStructure()
    {
        $token = Token::enqueue(SampleTokenValues::$customerId)
            ->generate(SampleTokenValues::$secretKey);

        $this->assertNotNull($token);
        $this->assertTrue(method_exists($token, 'getTokenIdentifier'), 'Expected getTokenIdentifier method to exist');
        $this->assertTrue(method_exists($token, 'getPayload'), 'Expected getPayload method to exist');
        $this->assertObjectHasProperty('customerId', $token);
        $this->assertObjectHasProperty('tokenVersion', $token);
        $this->assertObjectHasProperty('encryption', $token);
        $this->assertObjectHasProperty('issued', $token);
        $this->assertObjectHasProperty('expires', $token);
        $this->assertObjectHasProperty('eventId', $token);
    }

    public function testTokenParse()
    {

        $tokenObject = Token::parse(SampleTokenValues::$testToken, SampleTokenValues::$secretKey);

        $this->assertNotNull($tokenObject);
        $this->assertEquals(SampleTokenValues::$customDataKey, $tokenObject->getPayload()->getKey());
        $this->assertEquals(SampleTokenValues::$relativeQuality, $tokenObject->getPayload()->getRelativeQuality());
        $this->assertEquals(SampleTokenValues::$eventId, $tokenObject->eventId);
        $this->assertEquals(SampleTokenValues::$customData["color"], $tokenObject->getPayload()->getCustomData()['color']);
        $this->assertEquals(SampleTokenValues::$customData["size"], $tokenObject->getPayload()->getCustomData()['size']);
    }

    public function testParseTokenWithoutPayload()
    {
        $hash = "wUOdVDIKlrIqumpU33bShDPdvTkicRk3q4Z-Vs8epFc";
        $token = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MTUzOTEyOTYwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50IiwiaXAiOiI1LjcuOC42IiwieGZmIjoiNDUuNjcuMi40LDM0LjU2LjMuMiJ9.";
        $tokenString = $token . "." . $hash;

        $enqueueToken = Token::parse($tokenString, SampleTokenValues::$secretKey);

        $this->assertEquals("a21d423a-43fd-4821-84fa-4390f6a2fd3e", $enqueueToken->getTokenIdentifier());
        $this->assertEquals(SampleTokenValues::$customerId, $enqueueToken->customerId);
        $this->assertEquals(SampleTokenValues::$eventId, $enqueueToken->eventId);
        $this->assertEquals("5.7.8.6", $enqueueToken->ipAddress);
        $this->assertEquals("45.67.2.4,34.56.3.2", $enqueueToken->xForwardedFor);
        $this->assertEquals((new DateTime("2018-10-10T00:00:00Z"))->getTimestamp(), $enqueueToken->expires->getTimestamp());
        $this->assertEquals((new DateTime("2018-08-20T00:00:00Z"))->getTimestamp(), $enqueueToken->issued->getTimestamp());
        $this->assertEquals($hash, $enqueueToken->getHashCode());
        $this->assertEquals($token, $enqueueToken->getTokenWithoutHash());
        $this->assertEquals($tokenString, $enqueueToken->getToken());
        $this->assertEquals(EncryptionType::AES256, $enqueueToken->encryption);
        $this->assertEquals(TokenVersion::QT1, $enqueueToken->tokenVersion);
        $this->assertNull($enqueueToken->getPayload());
    }

    public function testParseTokenWithMinimalHeader()
    {
        $hash = "ChCRF4bTbt4zlOcvXLjQYouhgqgiNNNZqcci8VWoZIU";
        $token = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsInRpIjoiYTIxZDQyM2EtNDNmZC00ODIxLTg0ZmEtNDM5MGY2YTJmZDNlIiwiYyI6InRpY2tldGFuaWEifQ.";
        $tokenString = $token . "." . $hash;

        $enqueueToken = Token::parse($tokenString, SampleTokenValues::$secretKey);

        $this->assertEquals("a21d423a-43fd-4821-84fa-4390f6a2fd3e", $enqueueToken->getTokenIdentifier());
        $this->assertEquals(SampleTokenValues::$customerId, $enqueueToken->customerId);
        $this->assertNull($enqueueToken->eventId);
        $this->assertEquals(Utils::maxDate(), $enqueueToken->expires);
        $this->assertEquals((new DateTime("2018-08-20T00:00:00Z"))->getTimestamp(), $enqueueToken->issued->getTimestamp());
        $this->assertEquals($hash, $enqueueToken->getHashCode());
        $this->assertEquals($token, $enqueueToken->getTokenWithoutHash());
        $this->assertEquals($tokenString, $enqueueToken->getToken());
        $this->assertEquals(EncryptionType::AES256, $enqueueToken->encryption);
        $this->assertEquals(TokenVersion::QT1, $enqueueToken->tokenVersion);
        $this->assertNull($enqueueToken->getPayload());
    }

    public function testSignTokenWithMinimalHeader()
    {
        $expectedSignedToken = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MjUzNDAyMzAwNzk5MDAwLCJ0aSI6ImEyMWQ0MjNhLTQzZmQtNDgyMS04NGZhLTQzOTBmNmEyZmQzZSIsImMiOiJ0aWNrZXRhbmlhIn0..P5s3_Y9G4RvRq_xKNzCRKusCyqJHY4LUI0oY0Gu-di4";

        $token = EnqueueToken::create(
            "a21d423a-43fd-4821-84fa-4390f6a2fd3e",
            SampleTokenValues::$customerId,
            null,
            new \DateTime("2018-08-20T00:00:00Z"),
            null,
            null,
            null,
            null
        );

        $token->generate(SampleTokenValues::$secretKey, false);

        $actualSignedToken = $token->getToken();

        $this->assertEquals($expectedSignedToken, $actualSignedToken);
    }

    // public function testParseTokenWithAllFields()
    // {
    //     $tokenString = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOiIxNzYxMDUxODYzMDAxIiwiZXhwIjoiMTc5MjU4NzU5MzAwMCIsInRpIjoiNzBjYzQwMTEtODUwNS00NjA0LWFkYmItY2NjNThiMzI5ZWQyIiwiYyI6InRpY2tldGFuaWEiLCJlIjoiYnV5dGlja2V0dG9rZW4iLCJpcCI6IjgyLjE5Mi4xNzMuMzgiLCJ4ZmYiOiI4OC4xOTIuMTczLjg4In0.qOaYmEZ0KQEjLKPgj2X5XfdbZCpQ1tamDJZaP_hxOXTS6IxEV9fa7_pgQQopPEvsqUd7_VPLMhJRN3u6iKCgisWMO7UlLy1kkUqR8eP1k4QBBFOwiD_KIpfXGcul9yIHjvzQAxWgz9pF-qsmhQQjvg.ZGq8lnP3P92FEkQs3OPoxk--ED77QGs7fAm_QPKNXCM";

    //     $enqueueToken = Token::parse($tokenString, SampleTokenValues::$secretKey);

    //     // Validate parsed token
    //     $this->assertEquals('70cc4011-8505-4604-adbb-ccc58b329ed2', $enqueueToken->getTokenIdentifier());
    //     $this->assertEquals(SampleTokenValues::$customerId, $enqueueToken->customerId);
    //     $this->assertEquals('buytickettoken', $enqueueToken->eventId);
    //     $this->assertEquals('82.192.173.38', $enqueueToken->ipAddress);
    //     $this->assertEquals('88.192.173.88', $enqueueToken->xForwardedFor);
    //     $this->assertInstanceOf(DateTime::class, $enqueueToken->issued);
    //     $this->assertInstanceOf(DateTime::class, $enqueueToken->expires);
    //     $this->assertEquals(TokenVersion::QT1, $enqueueToken->tokenVersion);
    //     $this->assertEquals(EncryptionType::AES256, $enqueueToken->encryption);

    //     $this->assertNotNull($enqueueToken->getPayload());
    //     $payload = $enqueueToken->getPayload();
    //     $this->assertEquals('V1', $payload->getCustomData()['K1']);
    //     $this->assertEquals('V2', $payload->getCustomData()['K2']);
    //     $this->assertEquals(0.34, $payload->getRelativeQuality());
    //     $this->assertEquals('Connector', $payload->getTokenOrigin());

    //     $this->assertEquals('Key_3a76149d-3834-48d6-962a-d3362416854a', $payload->getKey());

    //     $this->assertEquals('2025-10-21 13:04:23', $enqueueToken->issued->format('Y-m-d H:i:s'));
    //     $this->assertEquals('2026-10-21 12:59:53', $enqueueToken->expires->format('Y-m-d H:i:s'));

    //     $this->assertNotEmpty($enqueueToken->getTokenWithoutHash());
    //     $this->assertEquals('ZGq8lnP3P92FEkQs3OPoxk--ED77QGs7fAm_QPKNXCM', $enqueueToken->getHashCode());
    // }

    public function testCreateTokenAndParse()
    {
        $secret = SampleTokenValues::$secretKey;

        $payload = Payload::enqueue()
            ->withKey('Key_3a76149d-3834-48d6-962a-d3362416854a')
            ->withRelativeQuality(0.34)
            ->withCustomData('K1', 'V1')
            ->withCustomData('K2', 'V2')
            ->withOrigin('Connector')
            ->generate();

        $issued = new DateTime('2025-10-21 13:04:23.001000');
        $expires = new DateTime('2026-10-21 12:59:53.000000');

        $token = EnqueueToken::create(
            '70cc4011-8505-4604-adbb-ccc58b329ed2',
            'ticketania',
            'buytickettoken',
            $issued,
            $expires,
            '82.192.173.38',
            '88.192.173.88',
            $payload
        );

        $token->generate($secret, false);

        $tokenString = $token->getToken();

        // Use this to verify manually if needed
        echo "Generated token: " . $tokenString . PHP_EOL;

        $parsed = Token::parse($tokenString, $secret);

        $this->assertEquals($token->getTokenIdentifier(), $parsed->getTokenIdentifier());
        $this->assertEquals($token->customerId, $parsed->customerId);
        $this->assertEquals($token->eventId, $parsed->eventId);
        $this->assertEquals($token->ipAddress, $parsed->ipAddress);
        $this->assertEquals($token->xForwardedFor, $parsed->xForwardedFor);
        $this->assertEquals($token->issued->getTimestamp(), $parsed->issued->getTimestamp());
        $this->assertEquals($token->expires->getTimestamp(), $parsed->expires->getTimestamp());
        $this->assertEquals($payload->getKey(), $parsed->getPayload()->getKey());
        $this->assertEquals($payload->getRelativeQuality(), $parsed->getPayload()->getRelativeQuality());
        $this->assertEquals($payload->getCustomData(), $parsed->getPayload()->getCustomData());
    }

    public function testSecondTokenGenerationDoesNotModifyFirstToken()
    {
        $generator = Token::enqueue(SampleTokenValues::$customerId);
        $token1 = $generator->withEventId("event1")->generate(SampleTokenValues::$secretKey);
        $token1String = $token1->getToken();

        $generator->withEventId("event2")->generate(SampleTokenValues::$secretKey);

        $parsedToken1 = Token::parse($token1String, SampleTokenValues::$secretKey);
        $this->assertEquals("event1", $parsedToken1->eventId);
    }
}
