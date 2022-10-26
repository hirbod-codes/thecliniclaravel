<?php

namespace App\PoliciesLogic\Order;

use App\PoliciesLogic\Order\ICalculateOrder;

interface ICalculateRegularOrder extends ICalculateOrder
{
    public function calculatePrice(): int;

    public function calculateTimeConsumption(): int;
}
