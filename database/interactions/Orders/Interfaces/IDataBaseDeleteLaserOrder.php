<?php

namespace Database\Interactions\Orders\Interfaces;

use App\Models\Order\LaserOrder;

interface IDataBaseDeleteLaserOrder
{
    public function deleteLaserOrder(LaserOrder $laserOrder): void;
}
