<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\RegularOrder;
use TheClinicDataStructures\DataStructures\Order\Regular\DSRegularOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseDeleteRegularOrder;

class DataBaseDeleteRegularOrder implements IDataBaseDeleteRegularOrder
{
    public function deleteRegularOrder(DSRegularOrder $regularOrder, DSUser $targetUser): void
    {
        if (($regularOrder = RegularOrder::query()->whereKey($regularOrder->getId())->first()) === null) {
            throw new \LogicException('Failed to find the requested regular order.', 404);
        }

        $regularOrder->delete();
    }
}
