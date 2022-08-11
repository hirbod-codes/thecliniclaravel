<?php

namespace App\UseCases\Visits\Creation;

use App\PoliciesLogic\Visit\IFindVisit;
use App\Models\Order\RegularOrder;
use App\Models\Visit\RegularVisit;
use App\UseCases\Visits\Interfaces\IDataBaseCreateRegularVisit;

class RegularVisitCreation
{
    public function create(RegularOrder $regularOrder, IDataBaseCreateRegularVisit $db, IFindVisit $iFindVisit): RegularVisit
    {
        return $db->createRegularVisit($regularOrder, $iFindVisit);
    }
}
