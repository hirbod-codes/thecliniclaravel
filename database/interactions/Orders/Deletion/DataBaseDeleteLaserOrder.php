<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\LaserOrder;
use TheClinicDataStructures\DataStructures\Order\Laser\DSLaserOrder;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicUseCases\Orders\Interfaces\IDataBaseDeleteLaserOrder;

class DataBaseDeleteLaserOrder implements IDataBaseDeleteLaserOrder
{
    public function deleteLaserOrder(DSLaserOrder $laserOrder, DSUser $targetUser): void
    {
        if (($laserOrder = LaserOrder::query()->whereKey($laserOrder->getId())->first()) === null) {
            throw new \LogicException('Failed to find the requested laser order.', 404);
        }

        $laserOrder->delete();
    }
}
