<?php

namespace App\PoliciesLogic\Order;

use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\PoliciesLogic\Order\ICalculateOrder;
use App\PoliciesLogic\Order\Laser\ILaserPriceCalculator;
use App\PoliciesLogic\Order\Laser\ILaserTimeConsumptionCalculator;

interface ICalculateLaserOrder extends ICalculateOrder
{
    public function calculatePrice(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator): int;

    public function calculateTimeConsumption(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserTimeConsumptionCalculator $timeConsumptionCalculator): int;

    public function calculatePriceWithoutDiscount(DSParts|null $parts = null, DSPackages|null $packages = null, ILaserPriceCalculator $priceCalculator): int;
}
