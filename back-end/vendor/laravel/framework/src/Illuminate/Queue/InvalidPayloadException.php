<?php
namespace Illuminate\Queue;
use InvalidArgumentException;
class InvalidPayloadException extends InvalidArgumentException
{
    public function __construct($message = null)
    {
        parent::__construct($message ?: json_last_error());
    }
}
