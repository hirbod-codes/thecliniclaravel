<?php

namespace Database\Interactions\Visits;

use App\Models\User;
use App\Models\Visit\RegularVisit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use TheClinicDataStructures\DataStructures\User\DSUser;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteRegularVisit;

class DataBaseDeleteRegularVisit implements IDataBaseDeleteRegularVisit
{
    public function deleteRegularVisit(DSRegularVisit $dsRegularVisit, DSUser $targetUser): void
    {
        $found = true;
        $user = User::query()->whereKey($targetUser->getId())->first();

        foreach ($user->orders as $order) {
            if (($regularOrder = $order->regularOrder) === null || count($regularVisits = $regularOrder->regularVisits) === 0) {
                continue;
            }

            foreach ($regularVisits as $regularVisit) {
                if ($regularVisit->getKey() === $dsRegularVisit->getId()) {
                    $found = true;
                    break;
                }
            }
        }

        if (!$found) {
            throw new ModelNotFoundException('', 404);
        }

        $regularVisit = RegularVisit::query()
            ->whereKey($dsRegularVisit->getId())
            ->first();

        $regularVisit->delete();
    }
}
