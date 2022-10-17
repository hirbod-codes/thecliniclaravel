<?php

namespace Database\Interactions\Visits\Interfaces;

use App\Models\Visit\LaserVisit;

interface IDataBaseDeleteLaserVisit extends IDataBaseDeleteVisit
{
    public function deleteLaserVisit(LaserVisit $laserVisit): void;
}
