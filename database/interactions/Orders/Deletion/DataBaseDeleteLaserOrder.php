<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\LaserOrder;
use App\Models\User;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteLaserOrder;

class DataBaseDeleteLaserOrder implements IDataBaseDeleteLaserOrder
{
    public function deleteLaserOrder(LaserOrder $laserOrder, User $targetUser): void
    {
        $laserOrder = LaserOrder::query()->whereKey($laserOrder->getKey())->firstOrFail();

        $laserOrder->delete();
    }
}
