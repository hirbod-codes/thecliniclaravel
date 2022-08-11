<?php

namespace App\PoliciesLogic\Order\Laser;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;

interface ILaserTimeConsumptionCalculator
{
    /**
     * Calculates the order's time consumption based on the provided parts and packages needed time and return the time with an integer.
     *
     * @param App\DataStructures\Order\DSParts $parts
     * @param App\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int;
}
