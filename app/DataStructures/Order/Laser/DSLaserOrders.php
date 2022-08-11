<?php

namespace App\DataStructures\Order\Laser;

use App\DataStructures\Order\DSOrder;
use App\DataStructures\Order\DSOrders;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

class DSLaserOrders extends DSOrders
{
    /**
     * @param \App\DataStructures\Order\Laser\DSLaserOrder $order
     * @return void
     *
     * @throws \App\DataStructures\Exceptions\Order\InvalidValueTypeException
     */
    protected function checkOrderType(DSOrder $order): void
    {
        if (!($order instanceof DSLaserOrder)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSLaserOrder::class . " as an array member.", 500);
        }
    }
}
