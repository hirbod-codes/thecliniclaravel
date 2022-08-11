<?php

namespace App\DataStructures\Order\Regular;

use App\DataStructures\Order\DSOrder;
use App\DataStructures\Order\DSOrders;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

class DSRegularOrders extends DSOrders
{
    /**
     * @param \App\DataStructures\Order\Regular\DSRegularOrder $order
     * @return void
     *
     * @throws \App\DataStructures\Exceptions\Order\InvalidValueTypeException
     */
    protected function checkOrderType(DSOrder $order): void
    {
        if (!($order instanceof DSRegularOrder)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSRegularOrder::class . " as an array member.", 500);
        }
    }
}
