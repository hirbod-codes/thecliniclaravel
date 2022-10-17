<?php

namespace Database\Interactions\Visits\Deletion;

use App\Models\Visit\LaserVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseDeleteLaserVisit;

class DataBaseDeleteLaserVisit implements IDataBaseDeleteLaserVisit
{
    public function deleteLaserVisit(LaserVisit $laserVisit): void
    {
        $laserVisit->delete();
    }
}
