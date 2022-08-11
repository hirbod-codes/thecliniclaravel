<?php

namespace App\UseCases\Visits\Deletion;

use App\Models\Visit\RegularVisit;
use App\UseCases\Visits\Interfaces\IDataBaseDeleteRegularVisit;

class RegularVisitDeletion
{
    public function delete(RegularVisit $regularVisit, IDataBaseDeleteRegularVisit $db): void
    {
        $db->deleteRegularVisit($regularVisit);
    }
}
