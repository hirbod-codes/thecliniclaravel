<?php

namespace Database\Interactions\Visits;

use App\Models\User;
use App\Models\Visit\LaserVisit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;

class DataBaseDeleteLaserVisit implements IDataBaseDeleteLaserVisit
{
    public function deleteLaserVisit(DSLaserVisit $dsLaserVisit, DSUser $targetUser): void
    {
        $found = true;
        $user = User::query()->whereKey($targetUser->getId())->first();

        foreach ($user->orders as $order) {
            if (($laserOrder = $order->laserOrder) === null) {
                continue;
            }

            foreach ($laserOrder->laserVisit as $laserVisit) {
                if ($laserVisit->getKey() === $dsLaserVisit->getId()) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            throw new ModelNotFoundException('', 404);
        }

        $laserVisit = LaserVisit::query()
            ->whereKey($dsLaserVisit->getId())
            ->first();

        $laserVisit->delete();
    }
}
