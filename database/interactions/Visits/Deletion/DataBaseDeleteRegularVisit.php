<?php

namespace Database\Interactions\Visits\Deletion;

use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseDeleteRegularVisit;

class DataBaseDeleteRegularVisit implements IDataBaseDeleteRegularVisit
{
    public function deleteRegularVisit(RegularVisit $regularVisit): void
    {
        $regularVisit->delete();
    }
}
