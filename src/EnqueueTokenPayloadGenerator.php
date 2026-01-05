<?php

namespace QueueIT\QueueToken;

class EnqueueTokenPayloadGenerator
{
    private $payload;

    public function __construct()
    {
        $this->payload = new EnqueueTokenPayload();
    }

    public function withKey(string $key): self
    {
        $this->payload->setKey($key);
        return $this;
    }

    public function withRelativeQuality(float $relativeQuality): self
    {
        $this->payload->setRelativeQuality($relativeQuality);
        return $this;
    }

    public function withCustomData(string $key, string $value): self
    {
        $this->payload->addCustomData($key, $value);
        return $this;
    }

    public function withOrigin(string $origin): self
    {
        $this->payload->setTokenOrigin($origin);
        return $this;
    }

    public function generate(): IEnqueueTokenPayload
    {
        return new EnqueueTokenPayload(
            $this->payload->getKey(),
            $this->payload->getRelativeQuality(),
            $this->payload->getCustomData(),
            $this->payload->getTokenOrigin()
        );
    }
}
