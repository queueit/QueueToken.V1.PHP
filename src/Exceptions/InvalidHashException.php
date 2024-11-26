<?php

namespace QueueIT\QueueToken\Exceptions;

class InvalidHashException extends TokenDeserializationException
{
    public function __construct()
    {
        parent::__construct("The token hash is invalid", null);
    }
}