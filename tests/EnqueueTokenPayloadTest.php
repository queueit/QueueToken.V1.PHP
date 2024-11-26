<?php

require_once __DIR__.'/../src/Payload.php';
require_once __DIR__.'/../src/Helpers/AESEncryption.php';
require_once __DIR__.'/../src/Helpers/Utils.php';
require_once __DIR__.'/../src/EnqueueTokenPayload.php';

use PHPUnit\Framework\TestCase;
use QueueIT\Helpers\Utils;
use QueueIT\QueueToken\Payload;
use QueueIT\QueueToken\EnqueueTokenPayload;


class EnqueueTokenPayloadTest extends TestCase
{
    public function testGenerateSimplePayload()
    {
        $expectedKey = "myKey";

        $instance = Payload::Enqueue()
            ->WithKey($expectedKey)
            ->Generate();
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

        $instance = Payload::Enqueue()
            ->WithKey($expectedKey)
            ->WithRelativeQuality($expectedRelativeQuality)
            ->Generate();
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

        $instance = Payload::Enqueue()
            ->WithKey($expectedKey)
            ->WithRelativeQuality($expectedRelativeQuality)
            ->WithCustomData("key", $expectedCustomDataValue)
            ->Generate();
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

        $instance = Payload::Enqueue()
            ->WithRelativeQuality($expectedRelativeQuality)
            ->Generate();
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

        $instance = Payload::Enqueue()
            ->WithRelativeQuality($expectedRelativeQuality)
            ->WithCustomData("key", $expectedCustomDataValue)
            ->Generate();
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

        $instance = Payload::Enqueue()
            ->WithCustomData("key", $expectedCustomDataValue)
            ->Generate();
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

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithCustomData("key1", "Value1")
            ->WithCustomData("key2", "Value2")
            ->WithCustomData("key3", "Value3")
            ->Generate();
        $serializedInstance = $instance->Serialize();
        $actualJson = Utils::uint8ArrayToString($serializedInstance);

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyRelativeQualityAndOneCustomData()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","cd":{"key1":"Value1"},"o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->WithCustomData("key1", "Value1")
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyAndRelativeQuality()
    {
        $expectedJson = '{"r":0.456,"k":"myKey","o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->WithRelativeQuality(0.456)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyOnly()
    {
        $expectedJson = '{"r":null,"k":"myKey","o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithKey("myKey")
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeKeyOnlyEscaped()
    {
        $expectedJson = '{"r":null,"k":"my\\"Key","o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithKey('my"Key')
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeRelativeQualityOnly()
    {
        $expectedJson = '{"r":0.456,"k":"","o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithRelativeQuality(0.456)
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize(), true);
        //$actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeCustomDataOnly()
    {
        $expectedJson = '{"r":null,"k":"","cd":{"key1":"Value1"},"o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithCustomData("key1", "Value1")
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testSerializeCustomDataEscaped()
    {
        $expectedJson = '{"r":null,"k":"","cd":{"ke\"y1":"Va\"lue1"},"o":"Connector"}';

        $instance = Payload::Enqueue()
            ->WithCustomData('ke"y1', 'Va"lue1')
            ->Generate();
        $actualJson = Utils::uint8ArrayToString($instance->Serialize());

        $this->assertEquals($expectedJson, $actualJson);
    }

    public function testEncryptedCorrectly()
    {
        $expectedEncryptedPayload = "0rDlI69F1Dx4Twps5qD4cQrbXbCRiezBd6fH1PVm6CnVY456FALkAhN3rgVrh_PGCJHcEXN5zoqFg65MH8WZcxl-G7_FAsZgEyBPRqsoJoylWJjVe-e1HI-voBaV7x6Q";
        
        $payload = Payload::Enqueue()
            ->WithKey("somekey")
            ->WithRelativeQuality(0.45678663514)
            ->WithCustomData("color", "blue")
            ->WithCustomData("size", "medium")
            ->Generate();
        
        $identifier = "a21d423a-43fd-4821-84fa-4390f6a2fd3e";
        $secretKey = "5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6";

        $actualEncryptedPayload = $payload->EncryptAndEncode($secretKey, $identifier);

        $decryptPayload = EnqueueTokenPayload::Deserialize($actualEncryptedPayload, $secretKey, $identifier);

        $this->assertEquals($expectedEncryptedPayload, $actualEncryptedPayload);
        $this->assertEquals($payload, $decryptPayload);
    }
}
