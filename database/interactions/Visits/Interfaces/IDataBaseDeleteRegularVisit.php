<?php

namespace Database\Interactions\Visits\Interfaces;

use App\Models\Visit\RegularVisit;

interface IDataBaseDeleteRegularVisit extends IDataBaseDeleteVisit
{
    public function deleteRegularVisit(RegularVisit $regularVisit): void;
}
