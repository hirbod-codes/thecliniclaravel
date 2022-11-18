<?php

namespace App\PoliciesLogic\Order\Laser;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;

interface ILaserPriceCalculator
{
    /**
     * Calculates the order's price based on the provided parts and packages costs and return the price with an integer.
     *
     * @param \App\DataStructures\Order\DSParts $parts
     * @param \App\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int;

    /**
     * Calculates the order's totall price without the discount of packages based on the provided parts and packages' parts costs and return the price with an integer.
     *
     * @param \App\DataStructures\Order\DSParts $parts
     * @param \App\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculateWithoutDiscount(DSParts $parts, DSPackages $packages): int;
}
