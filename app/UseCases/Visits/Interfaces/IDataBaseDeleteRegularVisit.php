<?php

namespace App\UseCases\Visits\Interfaces;

use App\Models\Visit\RegularVisit;

interface IDataBaseDeleteRegularVisit extends IDataBaseDeleteVisit
{
    public function deleteRegularVisit(RegularVisit $regularVisit): void;
}
