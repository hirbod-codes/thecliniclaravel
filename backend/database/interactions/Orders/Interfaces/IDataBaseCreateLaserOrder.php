<?php

namespace Database\Interactions\Orders\Interfaces;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Models\Order\LaserOrder;
use App\Models\User;

interface IDataBaseCreateLaserOrder
{
    public function createLaserOrder(User $targetUser, int $price, int $timeConsumption, int $priceWithoutDiscount, ?DSParts $parts = null, ?DSPackages $packages = null): LaserOrder;
}
