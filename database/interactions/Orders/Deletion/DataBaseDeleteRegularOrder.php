<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\RegularOrder;
use App\Models\User;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteRegularOrder;

class DataBaseDeleteRegularOrder implements IDataBaseDeleteRegularOrder
{
    public function deleteRegularOrder(RegularOrder $regularOrder, User $targetUser): void
    {
        $regularOrder = RegularOrder::query()->whereKey($regularOrder->getId())->firstOrFail();

        $regularOrder->delete();
    }
}
