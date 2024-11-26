<?php
namespace QueueIT\QueueToken;

require_once __DIR__ . '/Models/PayloadDto.php';
require_once __DIR__.'/Token.php';
require_once __DIR__ . '/Models/TokenOrigin.php';

require 'EnqueueTokenPayloadGenerator.php';


class Payload {
    public static function Enqueue() {
        return new EnqueueTokenPayloadGenerator();
    }
}
