<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\DataStructures\Time\DSDownTimes;

class DownTime
{
    /**
     * Moves $time to the first available up time, if it's in a down time. otherwise returns it as it is.
     *
     * @param \DateTime $time
     * @param \App\DataStructures\Time\DSDownTimes $dsDownTimes
     * @return void
     */
    public function moveTimeToClosestUpTime(\DateTime &$time, DSDownTimes $dsDownTimes): void
    {
        $pointerTS = $time->getTimestamp();

        if (!$this->isInDownTime($time, $dsDownTimes)) {
            return;
        }

        $newDSDownTimes = $dsDownTimes->cloneIt();

        /** @var \App\DataStructures\Time\DSDownTime $downTime */
        foreach ($newDSDownTimes as $downTime) {
            if ($pointerTS >= $downTime->getStartTimestamp() && $pointerTS < $downTime->getEndTimestamp()) {
                $time = $downTime->getEnd();
                return;
            }
        }

        throw new \RuntimeException("Failed to find down time object for this date time: " . $time->format("Y-m-d H:i:s l"), 500);
    }

    /**
     * @param \DateTime $dt
     * @param \App\DataStructures\Time\DSDownTimes $dsDownTimes
     * @return boolean
     */
    public function isInDownTime(\DateTime $dt, DSDownTimes $dsDownTimes): bool
    {
        $dtTS = $dt->getTimestamp();
        $newDSDownTimes = $dsDownTimes->cloneIt();

        /** @var \App\DataStructures\Time\DSDownTime $downTime */
        foreach ($newDSDownTimes as $downTime) {
            if ($dtTS < $downTime->getEndTimestamp() && $dtTS >= $downTime->getStartTimestamp()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param integer $firstTS
     * @param integer $lastTS
     * @param \App\DataStructures\Time\DSDownTimes $dsDownTimes
     * @return \App\DataStructures\Time\DSDownTimes
     */
    public function findDownTimeIntruptionWithTimeRange(int $firstTS, int $lastTS, DSDownTimes $dsDownTimes): DSDownTimes
    {
        $intruptingDsDownTimes = new DSDownTimes;
        $newDSDownTimes = $dsDownTimes->cloneIt();

        /** @var \App\DataStructures\Time\DSDownTime $dsDownTime */
        foreach ($newDSDownTimes as $dsDownTime) {
            if ($lastTS <= $dsDownTime->getStartTimestamp()) {
                break;
            }

            if ($firstTS < $dsDownTime->getEndTimestamp() && $lastTS > $dsDownTime->getStartTimestamp()) {
                $intruptingDsDownTimes[] = $dsDownTime;
            }
        }

        return $intruptingDsDownTimes;
    }
}
