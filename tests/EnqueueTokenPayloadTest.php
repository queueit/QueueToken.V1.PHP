<?php

namespace QueueIT\QueueToken\Tests;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\Helpers\Utils;
use QueueIT\QueueToken\Payload;
use QueueIT\QueueToken\EnqueueTokenPayload;
use QueueIT\QueueToken\Models\TokenOrigin;

class EnqueueTokenPayloadTest extends TestCase
{
    public function testGenerateSimplePayload()
    {
        $expectedKey = "myKey";

        $instance = Payload::enqueue()
            ->withKey($expectedKey)
            ->generate();
        $actualKey = $instance->getKey();
        $actualCustomData = $instance->getCustomData();

        $this->assertEquals($expectedKey, $actualKey);
        $this->assertNull($instance->getRelativeQuality());
        $this->assertNotNull($actualCustomData);
        $this->assertCount(0, $actualCustomData);
        $this->assertArrayNotHasKey("key", $instance->getCustomData());
    }

    public function testGeneratePayloadWithKeyAndRelativeQuality()
    {
        $expectedKey = "myKey";
        $expectedRelativeQuality = 0.456;

        $instance = Payload::enqueue()
            ->withKey($expectedKey)
            ->withRelativeQuality($expectedRelativeQuality)
            ->generate();
        $actualKey = $instance->getKey();
        $actualRelativeQuality = $instance->getRelativeQuality();
        $actualCustomData = $instance->getCustomData();

        $this->assertEquals($expectedKey, $actualKey);
        $this->assertEquals($expectedRelativeQuality, $actualRelativeQuality);
        $this->assertNotNull($actualCustomData);
        $this->assertCount(0, $actualCustomData);
        $this->assertArrayNotHasKey("key", $instance->getCustomData());
    }

    public function testGeneratePayloadWithRelativeQualityAndCustomData()
    {
        $expectedKey = "myKey";
        $expectedRelativeQuality = 0.456;
        $expectedCustomDataValue = "Value";

        $instance = Payload::enqueue()
            ->withKey($expectedKey)
            ->withRelativeQuality($expectedRelativeQuality)
            ->withCustomData("key", $expectedCustomDataValue)
            ->generate();
        $actualKey = $instance->getKey();
        $actualRelativeQuality = $instance->getRelativeQuality();
        $actualCustomData = $instance->getCustomData()["key"];

        $this->assertEquals($expectedKey, $actualKey);
        $this->assertEquals($expectedRelativeQuality, $actualRelativeQuality);
        $this->assertEquals($expectedCustomDataValue, $actualCustomData);
    }

    public function testGeneratePayloadWithRelativeQuality()
    {
        $expectedRelativeQuality = 0.456;

        $instance = Payload::enqueue()
            ->withRelativeQuality($expectedRelativeQuality)
            ->generate();
        $actualKey = $instance->getKey();
        $actualRelativeQuality = $instance->getRelativeQuality();
        $actualCustomData = $instance->getCustomData();

        $this->assertNull($actualKey);
        $this->assertEquals($expectedRelativeQuality, $actualRelativeQuality);
        $this->assertNotNull($actualCustomData);
        $this->assertCount(0, $actualCustomData);
    }

    public function testGeneratePayloadWithRelativeQualityAndCustomData2()
    {
        $expectedRelativeQuality = 0.456;
        $expectedCustomDataValue = "Value";

        $instance = Payload::enqueue()
            ->withRelativeQuality($expectedRelativeQuality)
            ->withCustomData("key", $expectedCustomDataValue)
            ->generate();
        $actualKey = $instance->getKey();
        $actualRelativeQuality = $instance->getRelativeQuality();
        $actualCustomData = $instance->getCustomData()["key"];

        $this->assertNull($actualKey);
        $this->assertEquals($expectedRelativeQuality, $actualRelativeQuality);
        $this->assertEquals($expectedCustomDataValue, $actualCustomData);
    }

    public function testGeneratePayloadOnlyWithCustomData()
    {
        $expectedCustomDataValue = "value";

        $instance = Payload::enqueue()
            ->withCustomData("key", $expectedCustomDataValue)
            ->generate();
        $actualKey = $instance->getKey();
        $actualRelativeQuality = $instance->getRelativeQuality();
        $actualCustomData = $instance->getCustomData()["key"];

        $this->assertNull($actualKey);
        $this->assertNull($actualRelativeQuality);
        $this->assertEquals($expectedCustomDataValue, $actualCustomData);
    }

    public function testSerializeKeyWithRelativeQualityAndMultipleCustomData()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","cd":{"key1":"Value1","key2":"Value2","key3":"Value3"},"o":"Connector"}';

        $instance = Payload::enqueue()
            ->withKey("myKey")
            ->withRelativeQuality(0.456)
            ->withCustomData("key1", "Value1")
            ->withCustomData("key2", "Value2")
            ->withCustomData("key3", "Value3")
            ->generate();
        $serializedInstance = $instance->serialize();
        $actualJson = Utils::uint8ArrayToString($serializedInstance);

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyRelativeQualityAndOneCustomData()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","cd":{"key1":"Value1"},"o":"Connector"}';

        $instance = Payload::enqueue()
            ->withKey("myKey")
            ->withRelativeQuality(0.456)
            ->withCustomData("key1", "Value1")
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyAndRelativeQuality()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","o":"Connector"}';

        $instance = Payload::enqueue()
            ->withKey("myKey")
            ->withRelativeQuality(0.456)
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyOnly()
    {
        $expectedJson = '{"r":null,"k":"myKey","o":"Connector"}';

        $instance = Payload::enqueue()
            ->withKey("myKey")
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyOnlyEscaped()
    {
        $expectedJson = '{"r":null,"k":"my\\"Key","o":"Connector"}';

        $instance = Payload::enqueue()
            ->withKey('my"Key')
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeRelativeQualityOnly()
    {
        $expectedJson = '{"r":0.456,"k":null,"o":"Connector"}';

        $instance = Payload::enqueue()
            ->withRelativeQuality(0.456)
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeCustomDataOnly()
    {
        $expectedJson = '{"r":null,"k":null,"cd":{"key1":"Value1"},"o":"Connector"}';

        $instance = Payload::enqueue()
            ->withCustomData("key1", "Value1")
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeCustomDataEscaped()
    {
        $expectedJson = '{"r":null,"k":null,"cd":{"ke\"y1":"Va\"lue1"},"o":"Connector"}';

        $instance = Payload::enqueue()
            ->withCustomData('ke"y1', 'Va"lue1')
            ->generate();
        $actualJson = Utils::uint8ArrayToString($instance->serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testEncryptedCorrectly()
    {
        $expectedEncryptedPayload = "0rDlI69F1Dx4Twps5qD4cQrbXbCRiezBd6fH1PVm6CnVY456FALkAhN3rgVrh_PGCJHcEXN5zoqFg65MH8WZcxl-G7_FAsZgEyBPRqsoJoylWJjVe-e1HI-voBaV7x6Q";

        $payload = Payload::enqueue()
            ->withKey("somekey")
            ->withRelativeQuality(0.45678663514)
            ->withCustomData("color", "blue")
            ->withCustomData("size", "medium")
            ->generate();

        $identifier = "a21d423a-43fd-4821-84fa-4390f6a2fd3e";
        $secretKey = "5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6";

        $actualEncryptedPayload = $payload->encryptAndEncode($secretKey, $identifier);

        $decryptPayload = EnqueueTokenPayload::deserialize($actualEncryptedPayload, $secretKey, $identifier);

        $this->assertEquals($expectedEncryptedPayload, $actualEncryptedPayload);
        $this->assertEquals($payload, $decryptPayload);
    }

    public function testGeneratePayloadWithInviteOnlyOrigin()
    {
        $expectedKey = "myKey";
        $expectedOrigin = TokenOrigin::INVITE_ONLY;

        $instance = Payload::Enqueue()
            ->WithKey($expectedKey)
            ->WithOrigin($expectedOrigin)
            ->Generate();

        $this->assertEquals($expectedKey, $instance->getKey());
        $this->assertEquals($expectedOrigin, $instance->getTokenOrigin());
    }

    public function testGeneratePayloadWithAkamaiBotManagerHeaderValidatorOrigin()
    {
        $expectedKey = "myKey";
        $expectedOrigin = TokenOrigin::AKAMAI_BOT_MANAGER_HEADER_VALIDATOR;

        $instance = Payload::Enqueue()
            ->WithKey($expectedKey)
            ->WithOrigin($expectedOrigin)
            ->Generate();

        $this->assertEquals($expectedKey, $instance->getKey());
        $this->assertEquals($expectedOrigin, $instance->getTokenOrigin());
    }

    public function testGeneratePayloadWithConnectorOrigin()
    {
        $expectedKey = "myKey";
        $expectedOrigin = TokenOrigin::CONNECTOR;

        $instance = Payload::Enqueue()
            ->WithKey($expectedKey)
            ->WithOrigin($expectedOrigin)
            ->Generate();

        $this->assertEquals($expectedKey, $instance->getKey());
        $this->assertEquals($expectedOrigin, $instance->getTokenOrigin());
    }

    public function testSerializeWithInviteOnlyOrigin()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","o":"InviteOnly"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithOrigin(TokenOrigin::INVITE_ONLY)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeWithAkamaiBotManagerHeaderValidatorOrigin()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","o":"AkamaiBotManagerHeaderValidator"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithOrigin(TokenOrigin::AKAMAI_BOT_MANAGER_HEADER_VALIDATOR)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeWithConnectorOrigin()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithOrigin(TokenOrigin::CONNECTOR)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeWithInviteOnlyOriginAndCustomData()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","cd":{"key1":"Value1","key2":"Value2"},"o":"InviteOnly"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithCustomData("key1", "Value1")
            ->WithCustomData("key2", "Value2")
            ->WithOrigin(TokenOrigin::INVITE_ONLY)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeWithAkamaiBotManagerOriginAndCustomData()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","cd":{"key1":"Value1","key2":"Value2"},"o":"AkamaiBotManagerHeaderValidator"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithCustomData("key1", "Value1")
            ->WithCustomData("key2", "Value2")
            ->WithOrigin(TokenOrigin::AKAMAI_BOT_MANAGER_HEADER_VALIDATOR)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }
}
