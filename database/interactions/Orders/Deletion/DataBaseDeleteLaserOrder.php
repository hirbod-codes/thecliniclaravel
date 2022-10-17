<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\LaserOrder;
use App\Models\User;
use Database\Interactions\Orders\Interfaces\IDataBaseDeleteLaserOrder;

class DataBaseDeleteLaserOrder implements IDataBaseDeleteLaserOrder
{
    public function deleteLaserOrder(LaserOrder $laserOrder): void
    {
        $laserOrder = LaserOrder::query()->whereKey($laserOrder->getKey())->firstOrFail();

        $laserOrder->delete();
    }
}
