<?php

namespace QueueIT\QueueToken;

use QueueIT\QueueToken\EnqueueTokenPayloadGenerator;

class Payload
{
    public static function enqueue()
    {
        return new EnqueueTokenPayloadGenerator();
    }
}
