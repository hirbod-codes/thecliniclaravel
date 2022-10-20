<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use Database\Interactions\Orders\Interfaces\IDataBaseDeleteRegularOrder;

class DataBaseDeleteRegularOrder implements IDataBaseDeleteRegularOrder
{
    public function deleteRegularOrder(RegularOrder $regularOrder): void
    {
        $regularOrder->order->deleteOrFail();
    }
}
