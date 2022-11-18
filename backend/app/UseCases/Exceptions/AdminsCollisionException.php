<?php

namespace App\UseCases\Exceptions;

class AdminsCollisionException extends \RuntimeException
{
    public function __construct(
        string $message = "An admin user is modifying another admin user which is forbidden.",
        int $code = 403
    ) {
        $this->message = $message;
        $this->code = $code;
    }
}
