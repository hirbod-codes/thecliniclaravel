<?php

namespace App\DataStructures\Visit\Regular;

use App\DataStructures\Order\DSOrder;
use App\DataStructures\Order\Regular\DSRegularOrder;
use App\DataStructures\Visit\DSVisit;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Exceptions\Visit\InvalidValueTypeException;

class DSRegularVisits extends DSVisits
{
    /**
     * @param \App\DataStructures\Visit\Regular\DSRegularVisit $visit
     * @return void
     *
     * @throws \App\DataStructures\Exceptions\Visit\InvalidValueTypeException
     */
    protected function validateVisitType(DSVisit $visit): void
    {
        if (!($visit instanceof DSRegularVisit)) {
            throw new InvalidValueTypeException("The new member must be an object of class: " . DSRegularVisit::class, 500);
        }
    }
}
