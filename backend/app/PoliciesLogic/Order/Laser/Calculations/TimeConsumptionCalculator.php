<?php

namespace App\PoliciesLogic\Order\Laser\Calculations;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\PoliciesLogic\Order\Laser\Calculations\TraitCollectDistinguishedParts;
use App\PoliciesLogic\Order\Laser\ILaserTimeConsumptionCalculator;

class TimeConsumptionCalculator implements ILaserTimeConsumptionCalculator
{
    use TraitCollectDistinguishedParts;

    /**
     * @param \App\DataStructures\Order\DSParts $parts
     * @param \App\DataStructures\Order\DSPackages $packages
     * @return integer
     */
    public function calculate(DSParts $parts, DSPackages $packages): int
    {
        $neededTime = 0;

        /** @var \App\DataStructures\Order\DSPart $part */
        foreach ($this->collectDistinguishedParts($parts, $packages) as $part) {
            $neededTime += $part->getNeededTime();
        }

        return $neededTime;
    }
}
