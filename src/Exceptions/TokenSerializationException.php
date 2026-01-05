<?php

namespace QueueIT\QueueToken\Exceptions;

use Exception;

class TokenSerializationException extends Exception
{
    public ?Exception $internalException;

    public function __construct(?Exception $ex = null)
    {
        parent::__construct("Exception serializing token");
        $this->internalException = $ex;
    }
}
