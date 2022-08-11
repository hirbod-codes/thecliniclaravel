<?php

namespace App\UseCases\Exceptions\Orders;

class UserTemptsToCreateOrderForAdminException extends \RuntimeException
{
    public function __construct(
        string $message = "Others cann't create an order for an admin user.",
        int $code = 403
    ) {
        $this->message = $message;
        $this->code = $code;
    }
}
