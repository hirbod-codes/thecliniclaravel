<?php

namespace App\UseCases\Visits\Creation;

use App\PoliciesLogic\Visit\IFindVisit;
use App\Models\Order\LaserOrder;
use App\Models\Visit\LaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;

class LaserVisitCreation
{
    public function create(LaserOrder $laserOrder, IDataBaseCreateLaserVisit $db, IFindVisit $iFindVisit): LaserVisit
    {
        return $db->createLaserVisit($laserOrder, $iFindVisit);
    }
}
