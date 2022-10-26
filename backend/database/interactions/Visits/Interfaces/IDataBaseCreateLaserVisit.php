<?php

namespace Database\Interactions\Visits\Interfaces;

use App\PoliciesLogic\Visit\IFindVisit;
use App\Models\Order\LaserOrder;
use App\Models\Visit\LaserVisit;

interface IDataBaseCreateLaserVisit extends IDataBaseCreateVisit
{
    public function createLaserVisit(LaserOrder $laserOrder, IFindVisit $iFindVisit): LaserVisit;
}
