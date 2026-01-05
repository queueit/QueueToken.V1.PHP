<?php

namespace QueueIT\QueueToken;

class Token
{
    public static function enqueue(string $customerId, ?string $tokenIdentifierPrefix = null): EnqueueTokenGenerator
    {
        return new EnqueueTokenGenerator($customerId, $tokenIdentifierPrefix);
    }

    public static function parse(string $token, string $secretKey): IEnqueueToken
    {
        return EnqueueToken::parse($token, $secretKey);
    }
}
