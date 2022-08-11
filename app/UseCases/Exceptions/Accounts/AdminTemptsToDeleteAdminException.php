<?php

namespace App\UseCases\Exceptions\Accounts;

class AdminTemptsToDeleteAdminException extends \RuntimeException
{
    public function __construct(
        string $message = "An admin user can not delete another admin user.",
        int $code = 403
    ) {
        $this->message = $message;
        $this->code = $code;
    }
}
