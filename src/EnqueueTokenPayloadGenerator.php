<?php

namespace QueueIT\QueueToken;

require 'EnqueueTokenPayload.php';

class EnqueueTokenPayloadGenerator
{
    private $_payload;

    public function __construct()
    {
        $this->_payload = new EnqueueTokenPayload();
    }

    public function WithKey($key)
    {
        $this->_payload = EnqueueTokenPayload::create($this->_payload, $key);
        return $this;
    }

    public function WithRelativeQuality($relativeQuality)
    {
        $this->_payload = EnqueueTokenPayload::create($this->_payload, null, $relativeQuality);
        return $this;
    }

    public function WithCustomData($key, $value)
    {
        $this->_payload = EnqueueTokenPayload::create($this->_payload, null);
        $this->_payload->AddCustomData($key, $value);
        return $this;
    }

    public function WithOrigin($origin)
    {
        $this->_payload = EnqueueTokenPayload::create($this->_payload, null);
        $this->_payload->AddTokenOrigin($origin);
        return $this;
    }

    public function Generate()
    {
        return $this->_payload;
    }
}