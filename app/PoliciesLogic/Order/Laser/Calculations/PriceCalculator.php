<?php

namespace App\PoliciesLogic\Order\Laser\Calculations;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\PoliciesLogic\Order\Laser\Calculations\TraitCollectDistinguishedParts;
use App\PoliciesLogic\Order\Laser\ILaserPriceCalculator as LaserILaserPriceCalculator;

class PriceCalculator implements LaserILaserPriceCalculator
{
    use TraitCollectDistinguishedParts;

    /**
     * It sums up the prices of $parts and $packages(with their discount, each package has a price lower than sum of all of it's contained parts).
     *
     * @param \App\DataStructures\Order\DSParts $parts
     * @param \App\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int
    {
        $price = 0;

        /** @var \App\DataStructures\Order\DSPackage $package */
        foreach ($packages as $package) {
            $price += $package->getPrice();
        }

        /** @var \App\DataStructures\Order\DSPart $part */
        foreach ($this->collectPartsThatDontExistInPackages($parts, $packages) as $part) {
            $price += $part->getPrice();
        }

        return $price;
    }

    /**
     * It sums up the costs of parts and parts in $packages as if there were no discount for any package.
     *
     * @param \App\DataStructures\Order\DSParts $parts
     * @param \App\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculateWithoutDiscount(DSParts $parts, DSPackages $packages): int
    {
        $price = 0;

        /** @var \App\DataStructures\Order\DSPart $part */
        foreach ($this->collectDistinguishedParts($parts, $packages) as $part) {
            $price += $part->getPrice();
        }

        return $price;
    }
}
