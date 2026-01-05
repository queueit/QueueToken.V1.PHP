<?php

namespace QueueIT\QueueToken;

use DateTime;

class EnqueueTokenGenerator
{
    private EnqueueToken $token;

    public function __construct(string $customerId, ?string $tokenIdentifierPrefix = null)
    {
        $this->token = new EnqueueToken($customerId, $tokenIdentifierPrefix);
    }

    public function withEventId(string $eventId): self
    {
        $this->token->eventId = $eventId;
        return $this;
    }

    public function withValidity(int $validityMilliSeconds): self
    {
        $currentTimestamp = $this->token->issued->getTimestamp();
        $expiryTimestamp = $currentTimestamp + ($validityMilliSeconds / 1000);
        $this->token->expires = (new DateTime())->setTimestamp($expiryTimestamp);
        return $this;
    }

    public function withValidityDate(DateTime $validity): self
    {
        $this->token->expires = $validity;
        return $this;
    }

    public function withPayload(IEnqueueTokenPayload $payload): self
    {
        $this->token->setPayload($payload);
        return $this;
    }

    public function withIpAddress(string $ip, ?string $xForwardedFor): self
    {
        $this->token->ipAddress = $ip;
        $this->token->xForwardedFor = $xForwardedFor;
        return $this;
    }

    public function generate(string $secretKey): IEnqueueToken
    {
        $this->token->generate($secretKey);
        $clone = $this->token;
        $this->token = EnqueueToken::create( // create a new instance, so that subsequent calls to Generate do not modify the previous one
            $this->token->getTokenIdentifier(),
            $this->token->customerId,
            $this->token->eventId,
            $this->token->issued,
            $this->token->expires,
            $this->token->ipAddress,
            $this->token->xForwardedFor,
            $this->token->getPayload()
        );
        return $clone;
    }
}
