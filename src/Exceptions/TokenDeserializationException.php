<?php

namespace QueueIT\QueueToken\Exceptions;

use Exception;

class TokenDeserializationException extends Exception
{
    public ?Exception $internalException;

    public function __construct(string $message, ?Exception $ex = null)
    {
        parent::__construct($message);
        $this->internalException = $ex;
    }
}
