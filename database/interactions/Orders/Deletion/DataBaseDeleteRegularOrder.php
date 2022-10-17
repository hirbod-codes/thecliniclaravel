<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Orders\Interfaces\IDataBaseDeleteRegularOrder;

class DataBaseDeleteRegularOrder implements IDataBaseDeleteRegularOrder
{
    public function deleteRegularOrder(RegularOrder $regularOrder): void
    {
        $regularOrder = RegularOrder::query()->whereKey($regularOrder->getId())->firstOrFail();

        $regularOrder->delete();
    }
}
