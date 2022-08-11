<?php

namespace App\UseCases\Orders\Creation;

use App\DataStructures\Order\Regular\DSRegularOrder;
use App\Models\Auth\User;
use App\Models\Order\RegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;

class RegularOrderCreation
{
    public function createRegularOrder(int $price, int $timeConsumption, User $targetUser, IDataBaseCreateRegularOrder $db,): RegularOrder
    {
        return $db->createRegularOrder($targetUser, $price, $timeConsumption);
    }

    public function createDefaultRegularOrder(User $targetUser, IDataBaseCreateDefaultRegularOrder $db): RegularOrder
    {
        return $db->createDefaultRegularOrder($targetUser);
    }
}
