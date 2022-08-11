<?php

namespace App\UseCases\Visits\Deletion;

use App\Models\Visit\LaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;

class LaserVisitDeletion
{
    public function delete(LaserVisit $laserVisit, IDataBaseDeleteLaserVisit $db): void
    {
        $db->deleteLaserVisit($laserVisit);
    }
}
