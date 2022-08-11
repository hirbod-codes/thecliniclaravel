<?php

namespace App\UseCases\Orders\Interfaces;

use App\DataStructures\Order\Regular\DSRegularOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;

interface IDataBaseCreateDefaultRegularOrder
{
    public function createDefaultRegularOrder(User $targetUser): RegularOrder;
}
