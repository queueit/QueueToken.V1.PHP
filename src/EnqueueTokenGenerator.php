<?php

namespace QueueIT\QueueToken;

use DateTime;

require 'EnqueueToken.php';
class EnqueueTokenGenerator
{
    private EnqueueToken $_token;

    public function __construct($customerId, $tokenIdentifierPrefix = null)
    {
        $this->_token = new EnqueueToken($customerId, $tokenIdentifierPrefix);
    }

    public function WithEventId($eventId): EnqueueTokenGenerator
    {
        $this->_token = EnqueueToken::AddEventId($this->_token, $eventId);
        return $this;
    }

    public function WithValidity($validityMilliSeconds): EnqueueTokenGenerator
    {
        $issuedEpochInMilliSeconds = $this->_token->Issued->getTimestamp() * 1000;
        $newEpochTimeInSeconds = ($issuedEpochInMilliSeconds + $validityMilliSeconds) / 1000;
        $newIssuedTime = DateTime::createFromFormat('U.u', sprintf('%.6f', $newEpochTimeInSeconds));
        $this->_token = EnqueueToken::AddExpires($this->_token, $newIssuedTime);
        return $this;
    }

    public function WithValidityDate($validity): EnqueueTokenGenerator
    {
        $this->_token = EnqueueToken::AddExpiresWithDate($this->_token, $validity);
        return $this;
    }

    public function WithPayload($payload): EnqueueTokenGenerator
    {
        $this->_token = EnqueueToken::AddPayload($this->_token, $payload);
        return $this;
    }

    public function WithIpAddress($ip, $xForwardedFor): EnqueueTokenGenerator
    {
        $this->_token = EnqueueToken::AddIPAddress($this->_token, $ip, $xForwardedFor);
        return $this;
    }

    public function Generate($secretKey): IEnqueueToken
    {
        $this->_token->Generate($secretKey);
        return $this->_token;
    }
}