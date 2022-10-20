<?php

namespace Database\Interactions\Orders\Deletion;

use App\Models\Order\LaserOrder;
use Database\Interactions\Orders\Interfaces\IDataBaseDeleteLaserOrder;

class DataBaseDeleteLaserOrder implements IDataBaseDeleteLaserOrder
{
    public function deleteLaserOrder(LaserOrder $laserOrder): void
    {
        $laserOrder->order->deleteOrFail();
    }
}
