<?php

namespace QueueIT\QueueToken;

require 'IEnqueueTokenPayload.php';

use QueueIT\QueueToken\Models\PayloadDto;
use QueueIT\Helpers\AESEncryption;
use QueueIT\Helpers\Base64UrlEncoding;
use QueueIT\QueueToken\Models\TokenOrigin;


class EnqueueTokenPayload implements IEnqueueTokenPayload
{
    private $_customData;
    private $_key;
    private $_relativeQuality;
    private $_origin;

    public function __construct()
    {
        $this->_customData = [];
        $this->_origin = TokenOrigin::CONNECTOR;
    }

    public function getKey()
    {
        return $this->_key;
    }

    private function setKey($value)
    {
        $this->_key = $value ?? null;
    }

    public function getCustomData()
    {
        return $this->_customData;
    }

    public function getRelativeQuality()
    {
        return $this->_relativeQuality;
    }

    private function setRelativeQuality($value)
    {
        $this->_relativeQuality = $value;
    }

    public function getTokenOrigin()
    {
        return $this->_origin ?? '';
    }

    public static function create($payload = null, $key = null, $relativeQuality = null, $customData = null, $origin = null)
    {
        $newPayload = new EnqueueTokenPayload();
        $newPayload->setKey($key);

        if ($payload) {
            $newPayload->setRelativeQuality($payload->getRelativeQuality());
            $newPayload->_customData = $payload->getCustomData();
            if (!$key || strlen($key) == 0) {
                $newPayload->setKey($payload->getKey());
            }
        }

        if ($relativeQuality !== null) {
            $newPayload->setRelativeQuality($relativeQuality);
        }

        if ($customData) {
            $newPayload->_customData = $customData;
        }

        if ($origin) {
            $newPayload->_origin = $origin;
        }

        return $newPayload;
    }

    public function AddCustomData($key, $value)
    {
        if (!$this->_customData) {
            $this->_customData = [];
        }
        $this->_customData[$key] = $value;
        return $this;
    }

    public function AddTokenOrigin($origin)
    {
        $this->_origin = $origin ?? TokenOrigin::CONNECTOR;
        return $this;
    }

    public function Serialize()
    {
        $dto = new PayloadDto();
        $dto->Key = $this->getKey();
        $dto->RelativeQuality = $this->getRelativeQuality();
        $dto->CustomData = $this->getCustomData();
        $dto->Origin = $this->getTokenOrigin();

        return $dto->Serialize();
    }

    public static function Deserialize($input, $secretKey, $tokenIdentifier)
    {
        $dto = PayloadDto::DeserializePayload($input, $secretKey, $tokenIdentifier);
        return EnqueueTokenPayload::create(null, $dto->Key, $dto->RelativeQuality, $dto->CustomData, $dto->Origin);
    }

    public function EncryptAndEncode($secretKey, $tokenIdentifier)
    {
        try {
            $serializedPayload = $this->Serialize();
            $encrypted = AESEncryption::EncryptPayload($secretKey, $tokenIdentifier, $serializedPayload);
            $base64 = Base64UrlEncoding::Encode($encrypted);
            return $base64;

            //return Base64::encode(new Uint8Array(unpack('C*', $encrypted)));
        } catch (Exception $ex) {
            throw new TokenSerializationException($ex->getMessage());
        }
    }

}