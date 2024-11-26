<?php

require_once __DIR__.'/../src/Helpers/AESEncryption.php';
require_once __DIR__.'/../src/Payload.php';
require_once __DIR__.'/../src/EnqueueTokenPayload.php';
require_once __DIR__ . '/../src/Models/EncryptionType.php';
require_once __DIR__ . '/../src/Models/TokenVersion.php';
require_once __DIR__.'/../src/Helpers/ShaHashing.php';
require_once __DIR__.'/../src/Token.php';
require_once __DIR__.'/../src/EnqueueTokenGenerator.php';

require 'TestValues.php';

use PHPUnit\Framework\TestCase;
use QueueIT\QueueToken\Token;
use QueueIT\Helpers\Utils;
use TestData\SampleTokenValues;
use QueueIT\QueueToken\Payload;
use QueueIT\QueueToken\Models\TokenVersion;
use QueueIT\QueueToken\Models\EncryptionType;
use QueueIT\QueueToken\EnqueueToken;
use QueueIT\QueueToken\Models\HeaderDto;

class EnqueueTokenTest extends TestCase
{
    public function testCreateSimpleToken()
    {
        $startTime = Utils::utcNow();
        $expectedCustomerId = "ticketania";
        $token = Token::Enqueue($expectedCustomerId)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $this->assertEquals($expectedCustomerId, $token->CustomerId);
        $this->assertNotNull($token->TokenIdentifier());
        $this->assertEquals(TokenVersion::QT1, $token->TokenVersion);
        $this->assertEquals(EncryptionType::AES256, $token->Encryption);
        $this->assertTrue($startTime <= $token->Issued->getTimestamp()*1000);
        $this->assertTrue(Utils::utcNow() >= $token->Issued);
        $this->assertEquals(Utils::maxDate(), $token->Expires->getTimestamp()*1000);
        $this->assertNull($token->EventId);
        $this->assertNull($token->Payload);
    }

    public function testCreateTokenWithIdentifierPrefix()
    {
        $tokenIdentifierPrefix = "SomePrefix";

        $token = Token::Enqueue("ticketania", $tokenIdentifierPrefix)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $tokenIdentifierParts = explode("~", $token->TokenIdentifier());
        $this->assertEquals($tokenIdentifierPrefix, $tokenIdentifierParts[0]);
    }

    public function testCreateTokenWithValidityAsLong()
    {
        $expectedValidity = 3000;

        $token = Token::Enqueue("ticketania")
            ->WithValidity($expectedValidity)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");
        $intervalInSeconds = $expectedValidity / 1000;
        $expectedExpiry = $token->Issued->modify("+{$intervalInSeconds} seconds");;
        $this->assertEquals($expectedExpiry->getTimestamp()*1000, $token->Expires->getTimestamp()*1000);
    }

    public function testCreateTokenWithValidityAsDate()
    {
        $expectedValidity = new DateTime("2030-02-01 12:00:00");

        $token = Token::Enqueue("ticketania")
            ->WithValidityDate($expectedValidity)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $this->assertEquals($expectedValidity->getTimestamp(), $token->Expires->getTimestamp());
    }

    public function testCreateTokenWithEventId()
    {
        $expectedEventId = "myevent";

        $token = Token::Enqueue("ticketania")
            ->WithEventId($expectedEventId)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $this->assertEquals($expectedEventId, $token->EventId);
    }

    public function testCreateTokenWithIpAddress()
    {
        $expectedIpAddress = "1.5.8.9";
        $expectedXForwardedFor = "45.67.2.4,34.56.3.2";

        $token = Token::Enqueue("ticketania")
            ->WithIpAddress($expectedIpAddress, $expectedXForwardedFor)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $this->assertEquals($expectedIpAddress, $token->IpAddress);
        $this->assertEquals($expectedXForwardedFor, $token->XForwardedFor);
    }

    public function testCreateTokenWithPayload()
    {
        $expectedPayload = Payload::Enqueue()->WithKey("somekey")->Generate();

        $token = Token::Enqueue("ticketania")
            ->WithPayload($expectedPayload)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $this->assertEquals($expectedPayload, $token->getPayload());
    }

    public function testCreateTokenWithPayloadKeyAndRelativeQuality()
    {
        $expectedEventId = "myevent";
        $expectedCustomerId = "ticketania";
        $expectedValidity = 3000;

        $expectedPayload = Payload::Enqueue()->WithKey("somekey")->Generate();

        $token = Token::Enqueue($expectedCustomerId)
            ->WithPayload($expectedPayload)
            ->WithEventId($expectedEventId)
            ->WithValidity($expectedValidity)
            ->Generate("5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6");

        $this->assertEquals($expectedCustomerId, $token->CustomerId);
        $this->assertEquals($expectedEventId, $token->EventId);
        $this->assertEquals($expectedValidity, $token->Expires->getTimestamp()*1000 - $token->Issued->getTimestamp()*1000);
        $this->assertEquals($expectedPayload, $token->getPayload());
    }

//TODO: Use this to test the hashing algorithm
    public function testSignTokenWithoutPayload()
    {
        $expectedSignedToken = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MTUzOTEyOTYwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50IiwiaXAiOiI1LjcuOC42IiwieGZmIjoiNDUuNjcuMi40LDM0LjU2LjMuMiJ9..wUOdVDIKlrIqumpU33bShDPdvTkicRk3q4Z-Vs8epFc";


        $token = EnqueueToken::Create(
            "a21d423a-43fd-4821-84fa-4390f6a2fd3e",
            "ticketania",
            "myevent",
            new DateTime("2018-08-20T00:00:00Z"), //TODO: be sure the values for the test are correct.
            new DateTime("2018-10-10T00:00:00Z"),
            "5.7.8.6",
            "45.67.2.4,34.56.3.2",
            null
        );
        $token->Generate(SampleTokenValues::$SecretKey, false);

        $actualSignedToken = $token->getToken();

        $this->assertEquals($expectedSignedToken,$actualSignedToken);
    }


    public function testSignTokenWithPayloadAndCustomData()
    {
        $expectedSignedToken = SampleTokenValues::$TestToken;

        $payload = Payload::Enqueue()
            ->WithKey("somekey")
            ->WithRelativeQuality(0.45678663514)
            ->WithCustomData("color", "blue")
            ->WithCustomData("size", "medium")
            ->Generate();

        $token = EnqueueToken::Create(
            "a21d423a-43fd-4821-84fa-4390f6a2fd3e",
            "ticketania",
            "myevent",
            new DateTime("2018-08-20T00:00:00Z"),
            new DateTime("2018-10-10T00:00:00Z"),
        null,
            null,
            $payload
        );
        $token->Generate(SampleTokenValues::$SecretKey, false);

        $actualSignedToken = $token->getToken();

        $this->assertEquals($expectedSignedToken,$actualSignedToken);
    }

    public function testSerializeHeaders()  // Successful JR. 11/4/24
    {
        $expectedText = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MTUzOTEyOTYwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50IiwiaXAiOiI1LjcuOC42IiwieGZmIjoiNDUuNjcuMi40LDM0LjU2LjMuMiJ9.";
        $dto = new HeaderDto();
        $dto->CustomerId = "ticketania";
        $dto->EventId = "myevent";
        $dto->TokenIdentifier = "a21d423a-43fd-4821-84fa-4390f6a2fd3e";
        //$dto->Issued = new DateTime("2018-08-20T00:00:00Z");
        $issuedDateTime = new dateTime("2018-08-20T00:00:00Z");
        $issuedDateTimeMicro = $issuedDateTime->getTimestamp()*1000;
        $dto->Issued = $issuedDateTimeMicro;
        $expiresTime=new DateTime("2018-10-10T00:00:00Z");
        $expTimeMicro=$expiresTime->getTimestamp()*1000;
        $dto->Expires = $expTimeMicro;
        //$dto->Issuer = "myIssuer";
        $dto->Encryption = EncryptionType::AES256;
        $dto->TokenVersion = TokenVersion::QT1;
        $dto->IpAddress = "5.7.8.6";
        $dto->XForwardedFor = "45.67.2.4,34.56.3.2";
        $actualText = $dto->Serialize().".";

        $this->assertEquals($expectedText, $actualText);
    }

    public function testTokenStructure()
    {
        $token = Token::Enqueue(SampleTokenValues::$CustomerId)
            ->Generate(SampleTokenValues::$SecretKey);

        $this->assertNotNull($token);
        $this->assertTrue(method_exists($token, 'TokenIdentifier'), 'Expected TokenIdentifier method to exist');
        $this->assertTrue(method_exists($token,'getPayload'),'Exptected getPayload method to exist');
        $this->assertObjectHasProperty('CustomerId', $token);
        $this->assertObjectHasProperty('TokenVersion', $token);
        $this->assertObjectHasProperty('Encryption', $token);
        $this->assertObjectHasProperty('Issued', $token);
        $this->assertObjectHasProperty('Expires', $token);
        $this->assertObjectHasProperty('EventId', $token);

    }

    public function testTokenParse(){

        $tokenObject = Token::parse(SampleTokenValues::$TestToken, SampleTokenValues::$SecretKey);

        $this->assertNotNull($tokenObject);
        $this->assertEquals(SampleTokenValues::$CustomDataKey, $tokenObject->getPayload()->getKey());
        $this->assertEquals(SampleTokenValues::$RelativeQuality, $tokenObject->getPayload()->getRelativeQuality());
        $this->assertEquals(SampleTokenValues::$EventId, $tokenObject->EventId);
        $this->assertEquals(SampleTokenValues::$CustomData["color"], $tokenObject->getPayload()->getCustomData()['color']);
        $this->assertEquals(SampleTokenValues::$CustomData["size"], $tokenObject->getPayload()->getCustomData()['size']);
    }
}

