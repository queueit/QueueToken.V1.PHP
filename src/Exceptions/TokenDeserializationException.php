<?php

namespace QueueIT\QueueToken\Exceptions;

//use QueueIT\QueueToken\Exception;

use Exception;

class TokenDeserializationException extends Exception
{
    public $InternalException;

    public function __construct($message, $ex)
    {
        parent::__construct($message);
        $this->InternalException = $ex;
    }
}