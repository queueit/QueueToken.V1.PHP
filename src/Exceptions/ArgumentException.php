<?php

namespace QueueIT\QueueToken\Exceptions;

use Exception;

class ArgumentException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}