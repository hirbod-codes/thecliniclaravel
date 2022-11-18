<?php

namespace App\PoliciesLogic\Order\Regular;

use App\PoliciesLogic\Order\ICalculateRegularOrder;

class RegularOrder implements ICalculateRegularOrder
{
    public function calculatePrice(): int
    {
        return 400000;
    }

    public function calculateTimeConsumption(): int
    {
        return 600;
    }
}
