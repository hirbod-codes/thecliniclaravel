<?php

namespace App\DataStructures\Visit\Laser;

use App\DataStructures\Order\DSOrder;
use App\DataStructures\Order\Laser\DSLaserOrder;
use App\DataStructures\Visit\DSVisit;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Exceptions\Visit\InvalidValueTypeException;

class DSLaserVisits extends DSVisits
{
    /**
     * @param \App\DataStructures\Visit\Laser\DSLaserVisit $visit
     * @return void
     *
     * @throws \App\DataStructures\Exceptions\Visit\InvalidValueTypeException
     */
    protected function validateVisitType(DSVisit $visit): void
    {
        if (!($visit instanceof DSLaserVisit)) {
            throw new InvalidValueTypeException("The new member must be an object of class: " . DSLaserVisit::class, 500);
        }
    }
}
