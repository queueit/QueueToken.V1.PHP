<?php

namespace QueueIT\QueueToken;

require 'Models\HeaderDto.php';

class Token
{
    public static function Enqueue($customerId, $tokenIdentifierPrefix = null)
    {
        return new EnqueueTokenGenerator($customerId, $tokenIdentifierPrefix);
    }

    public static function Parse($token, $secretKey): IEnqueueToken
    {
        return EnqueueToken::Parse($token, $secretKey);
    }
}
