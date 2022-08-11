<?php

namespace App\UseCases\Orders\Deletion;

use App\Models\Auth\User;
use App\Models\Order\LaserOrder;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteLaserOrder;

class LaserOrderDeletion
{
    public function deleteLaserOrder(LaserOrder $laserOrder, User $targetUser, IDataBaseDeleteLaserOrder $db): void
    {
        $db->deleteLaserOrder($laserOrder, $targetUser);
    }
}
