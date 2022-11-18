<?php

namespace App\DataStructures\Order\Regular;

use App\DataStructures\Order\DSOrder;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Visit\Regular\DSRegularVisits;
use App\DataStructures\Exceptions\Order\InvalidValueTypeException;

class DSRegularOrder extends DSOrder
{
    protected function validateVisitsType(DSVisits|null $visits): void
    {
        if ($visits === null) {
            return;
        }

        if (!($visits instanceof DSRegularVisits)) {
            throw new InvalidValueTypeException("This data structure only accepts the type: " . DSRegularVisits::class . " as it's associated visits.", 500);
        }
    }
}
