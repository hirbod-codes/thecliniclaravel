<?php

namespace App\UseCases\Exceptions\Orders;

class AdminTemptsToCreateOrderForAdminException extends \RuntimeException
{
    public function __construct(
        string $message = "An admin user can not create an order for another admin user.",
        int $code = 403
    ) {
        $this->message = $message;
        $this->code = $code;
    }
}
