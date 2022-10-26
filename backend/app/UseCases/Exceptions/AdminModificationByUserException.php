<?php

namespace App\UseCases\Exceptions;

class AdminModificationByUserException extends \RuntimeException
{
    public function __construct(
        string $message = "A user's trying to modify admin information, Forbidden.",
        int $code = 403
    ) {
        $this->message = $message;
        $this->code = $code;
    }
}
