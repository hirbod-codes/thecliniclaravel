<?php

namespace App\DataStructures\Exceptions\User;


class NoPrivilegeFoundException extends UserExceptions
{
    public function __construct($message = "Failed to find the requested privilege.", int $code = 500, \Throwable|null $previous = null)
    {
        parent::__construct($message, 500, $previous);
    }
}
