<?php

namespace Database\Interactions\Visits;

use App\Models\Visit\LaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;

class DataBaseDeleteLaserVisit implements IDataBaseDeleteLaserVisit
{
    public function deleteLaserVisit(LaserVisit $laserVisit): void
    {
        $laserVisit->delete();
    }
}
