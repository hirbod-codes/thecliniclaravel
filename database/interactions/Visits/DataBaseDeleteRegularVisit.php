<?php

namespace Database\Interactions\Visits;

use App\Models\Visit\RegularVisit;
use App\UseCases\Visits\Interfaces\IDataBaseDeleteRegularVisit;

class DataBaseDeleteRegularVisit implements IDataBaseDeleteRegularVisit
{
    public function deleteRegularVisit(RegularVisit $regularVisit): void
    {
        $regularVisit->delete();
    }
}
