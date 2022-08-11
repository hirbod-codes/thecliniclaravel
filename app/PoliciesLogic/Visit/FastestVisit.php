<?php

namespace App\PoliciesLogic\Visit;

use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Visit\DSVisits;
use App\DataStructures\Time\DSWorkSchedule;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\PoliciesLogic\Exceptions\Visit\VisitSearchFailure;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\Utilities\SearchingBetweenDownTimes;
use App\PoliciesLogic\Visit\Utilities\ValidateTimeRanges;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;

class FastestVisit implements IFindVisit
{
    private \DateTime $pointer;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWorkSchedule $dsWorkSchedule;

    private DSDownTimes $dsDownTimes;

    private SearchingBetweenDownTimes $SearchingBetweenDownTimes;

    private ValidateTimeRanges $validateTimeRanges;

    private string $oldSort;

    public function __construct(
        \DateTime $startPoint,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWorkSchedule $dsWorkSchedule,
        DSDownTimes $dsDownTimes,
        null|SearchingBetweenDownTimes $SearchingBetweenDownTimes = null,
        null|ValidateTimeRanges $validateTimeRanges = null
    ) {
        $this->pointer = $startPoint;
        $this->consumingTime = $consumingTime;
        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;
        $this->dsWorkSchedule = $dsWorkSchedule;
        $this->dsDownTimes = $dsDownTimes;
        $this->SearchingBetweenDownTimes = $SearchingBetweenDownTimes ?: new SearchingBetweenDownTimes();
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
    }

    public function findVisit(): int
    {
        $this->validateTimeRanges->checkConsumingTimeInWorkSchedule($this->dsWorkSchedule, $this->consumingTime);
        $recursiveSafetyLimit = 0;

        while (!isset($timestamp) && $recursiveSafetyLimit < 500) {
            $newDSWorkSchedule = $this->dsWorkSchedule->cloneIt();
            $newDSWorkSchedule->setStartingDay($this->pointer->format("l"));

            /** @var DSDateTimePeriods $periods */
            foreach ($newDSWorkSchedule as $weekDay => $periods) {
                /** @var DSDateTimePeriod $period */
                foreach ($periods as $period) {
                    $periodStartTS = (new \DateTime($this->pointer->format('Y-m-d') . ' ' . $period->getStart()->format('H:i:s')))->getTimestamp();
                    $periodEndTS = (new \DateTime($this->pointer->format('Y-m-d') . ' ' . $period->getEnd()->format('H:i:s')))->getTimestamp();

                    if ($this->pointer->getTimestamp() > $periodEndTS) {
                        continue;
                    }

                    if ($this->pointer->getTimestamp() <= $periodStartTS) {
                        $firstTS = $periodStartTS;
                    } else {
                        $firstTS = $this->pointer->getTimestamp();
                    }

                    if (($periodEndTS - $firstTS) < $this->consumingTime) {
                        continue;
                    }

                    try {
                        $timestamp = $this->SearchingBetweenDownTimes->search(
                            $firstTS,
                            $periodEndTS,
                            $this->futureVisits,
                            $this->dsDownTimes,
                            $this->consumingTime
                        );
                        // For testing purposes
                        $t = (new \DateTime)->setTimestamp($timestamp);
                        break 2;
                    } catch (VisitSearchFailure $th) {
                    } catch (NeededTimeOutOfRange $th) {
                    }
                }
                $this->pointer
                    ->setTime(0, 0)
                    ->modify('+1 day');
            }

            $recursiveSafetyLimit++;
        }

        $this->futureVisits->setSort($this->oldSort);

        if (isset($timestamp)) {
            return $timestamp;
        } else {
            throw new \LogicException('Failed to find a visit time.', 500);
        }
    }
}
