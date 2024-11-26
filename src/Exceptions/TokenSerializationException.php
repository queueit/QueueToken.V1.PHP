<?php

namespace QueueIT\QueueToken\Exceptions;

use Exception;

class TokenSerializationException extends Exception
{
    public $InternalException;

    public function __construct($ex)
    {
        parent::__construct("Exception serializing token");
        $this->InternalException = $ex;
    }
}