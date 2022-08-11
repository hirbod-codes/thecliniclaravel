<?php

namespace App\UseCases\Visits\Interfaces;

use App\PoliciesLogic\Visit\IFindVisit;
use App\Models\Order\RegularOrder;
use App\Models\Visit\RegularVisit;

interface IDataBaseCreateRegularVisit extends IDataBaseCreateVisit
{
    public function createRegularVisit(RegularOrder $regularOrder, IFindVisit $iFindVisit): RegularVisit;
}
