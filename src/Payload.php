<?php

namespace QueueIT\QueueToken;

class Payload
{
    public static function enqueue()
    {
        return new EnqueueTokenPayloadGenerator();
    }
}
