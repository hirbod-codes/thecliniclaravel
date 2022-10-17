<?php

namespace Database\Interactions\Orders\Interfaces;

use App\Models\Order\LaserOrder;
use App\Models\User;


interface IDataBaseDeleteLaserOrder
{
    public function deleteLaserOrder(LaserOrder $laserOrder): void;
}
