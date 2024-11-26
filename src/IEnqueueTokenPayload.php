<?php

namespace QueueIT\QueueToken;
interface IEnqueueTokenPayload
{
    public function getKey();

    public function getRelativeQuality();

    public function getCustomData();

    public function getTokenOrigin();

    public function EncryptAndEncode($secretKey, $tokenIdentifier);

    public function Serialize();
}