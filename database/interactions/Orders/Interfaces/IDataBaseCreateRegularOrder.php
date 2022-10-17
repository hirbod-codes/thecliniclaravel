<?php

namespace Database\Interactions\Orders\Interfaces;

use App\DataStructures\Order\Regular\DSRegularOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;

interface IDataBaseCreateRegularOrder
{
    public function createRegularOrder(User $targetUser, int $price, int $timeConsumption): RegularOrder;
}
