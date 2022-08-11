<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWorkSchedule;

class WorkSchedule
{
    /**
     * Moves $pointer to the nearest work schedule, if it's not in work schedule hours. otherwise returns it as it is.
     *
     * @param \DateTime $pointer
     * @param DSWorkSchedule $dsWorkSchedule
     * @return void
     */
    public function movePointerToClosestWorkSchedule(\DateTime &$pointer, DSWorkSchedule $dsWorkSchedule): void
    {
        if ($this->isInWorkSchedule($pointer, $dsWorkSchedule)) {
            return;
        }

        $pointerTS = $pointer->getTimestamp();
        $newDSWorkSchedule = $dsWorkSchedule->cloneIt();
        $newDSWorkSchedule->setStartingDay($pointer->format("l"));

        /** @var DSDateTimePeriods $periods */
        foreach ($newDSWorkSchedule as $weekDay => $periods) {
            /** @var DSDateTimePeriod $period */
            foreach ($periods as $period) {
                if ($pointerTS < $period->getStartTimestamp()) {
                    $pointer->setTimestamp($period->getStartTimestamp());

                    return;
                }
            }

            if ($pointerTS >= $period->getEndTimestamp()) {
                $newDSWorkSchedule->next();
                if ($newDSWorkSchedule->valid()) {
                    $pointer->setTimestamp($newDSWorkSchedule->current()[0]->getStartTimestamp());
                    return;
                }
            }

            throw new \LogicException("Failed to find the right work schedule.", 500);
        }
    }

    /**
     * @param \DateTime $dt
     * @param DSWorkSchedule $dsWorkSchedule
     * @return boolean
     */
    public function isInWorkSchedule(\DateTime $dt, DSWorkSchedule $dsWorkSchedule): bool
    {
        $dtTS = $dt->getTimestamp();
        $newDSWorkSchedule = $dsWorkSchedule->cloneIt();
        $newDSWorkSchedule->setStartingDay($dt->format("l"));

        /** @var DSDateTimePeriods $periods */
        foreach ($newDSWorkSchedule as $periods) {
            /** @var DSDateTimePeriod $period */
            foreach ($periods as $period) {
                if ($dtTS < $period->getEndTimestamp() && $dtTS >= $period->getStartTimestamp()) {
                    return true;
                }
            }

            break;
        }

        return false;
    }
}
