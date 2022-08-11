<?php

namespace App\UseCases\Exceptions\Accounts;

class AdminTemptsToUpdateAdminException extends \RuntimeException
{
    public function __construct(
        string $message = "An admin user can not update another admin user.",
        int $code = 403
    ) {
        $this->message = $message;
        $this->code = $code;
    }
}
