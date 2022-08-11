<?php

namespace App\UseCases\Orders\Creation;

use App\PoliciesLogic\Exceptions\Order\InvalidGenderException;
use App\PoliciesLogic\Exceptions\Order\NoPackageOrPartException;
use App\PoliciesLogic\Order\ICalculateLaserOrder;
use App\PoliciesLogic\Order\Laser\Calculations\PriceCalculator;
use App\PoliciesLogic\Order\Laser\Calculations\TimeConsumptionCalculator;
use App\PoliciesLogic\Order\Laser\ILaserPriceCalculator;
use App\PoliciesLogic\Order\Laser\ILaserTimeConsumptionCalculator;
use App\PoliciesLogic\Order\Laser\LaserOrder;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Models\Auth\User;
use App\Models\Order\LaserOrder as LaserOrderModel;
use App\UseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;

class LaserOrderCreation
{
    private ICalculateLaserOrder $iCalculateLaserOrder;

    private ILaserPriceCalculator $iLaserPriceCalculator;

    private ILaserTimeConsumptionCalculator $iLaserTimeConsumptionCalculator;

    public function __construct(
        ICalculateLaserOrder $iCalculateLaserOrder = null,
        ILaserPriceCalculator $iLaserPriceCalculator = null,
        ILaserTimeConsumptionCalculator $iLaserTimeConsumptionCalculator = null
    ) {
        $this->iLaserPriceCalculator = $iLaserPriceCalculator ?: new PriceCalculator;
        $this->iLaserTimeConsumptionCalculator = $iLaserTimeConsumptionCalculator ?: new TimeConsumptionCalculator;
        $this->iCalculateLaserOrder = $iCalculateLaserOrder ?: new LaserOrder;
    }

    public function createLaserOrder(User $targetUser, IDataBaseCreateLaserOrder $db, ?DSParts $parts = null, ?DSPackages $packages = null): LaserOrderModel
    {
        if (($parts !== null && $targetUser->getGender() !== $parts->getGender()) || ($packages !== null && $targetUser->gender !== $packages->getGender())) {
            throw new InvalidGenderException("User, parts and packages must have the same gender.", 500);
        } elseif ($parts === null && $packages === null) {
            throw new NoPackageOrPartException("One of the parts or packages must exist.", 500);
        }

        return $db->createLaserOrder(
            $targetUser,
            $this->iCalculateLaserOrder->calculatePrice($parts, $packages, $this->iLaserPriceCalculator),
            $this->iCalculateLaserOrder->calculateTimeConsumption($parts, $packages, $this->iLaserTimeConsumptionCalculator),
            $this->iCalculateLaserOrder->calculatePriceWithoutDiscount($parts, $packages, $this->iLaserPriceCalculator),
            $parts,
            $packages
        );
    }
}
