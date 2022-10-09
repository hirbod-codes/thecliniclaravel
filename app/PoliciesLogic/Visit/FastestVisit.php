<?php

namespace App\PoliciesLogic\Visit;

use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Visit\DSVisits;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\Utilities\SearchingBetweenDownTimes;
use App\PoliciesLogic\Visit\Utilities\ValidateTimeRanges;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Time\Util\TimeDSConverter;
use App\DataStructures\Visit\DSVisit;
use App\PoliciesLogic\Exceptions\Visit\InvalidConsumingTime;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\PoliciesLogic\Visit\Utilities\TimePeriodsManager;

class FastestVisit implements IFindVisit
{
    private \DateTime $pointer;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWeeklyTimePatterns $workSchedule;

    private DSDownTimes $dsDownTimes;

    private TimePeriodsManager $timePeriodsManager;

    private ValidateTimeRanges $validateTimeRanges;

    private string $oldSort;

    public function __construct(
        \DateTime $startPoint,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWeeklyTimePatterns $workSchedule,
        DSDownTimes $dsDownTimes,
        null|TimePeriodsManager $timePeriodsManager = null,
        null|ValidateTimeRanges $validateTimeRanges = null
    ) {
        $this->pointer = $startPoint;
        $this->consumingTime = $consumingTime;
        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;
        $this->workSchedule = $workSchedule;
        $this->dsDownTimes = $dsDownTimes;

        $this->timePeriodsManager = $timePeriodsManager ?: new TimePeriodsManager();
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges();
    }

    /**
     * @return integer
     * @throws NeededTimeOutOfRange if consuming time is bigger than all of the work schedule time patterns
     */
    public function findVisit(): int
    {
        try {
            $this->validateTimeRanges->checkConsumingTimeInWorkSchedule($this->workSchedule, $this->consumingTime);
            $pointer = (new \DateTime)->setTimestamp($this->pointer->getTimestamp());

            while (1) {

                /** @var DSTimePattern $timePattern */
                foreach ($this->workSchedule[$pointer->format("l")] as $timePattern) {
                    $startTS = (new \DateTime($pointer->format("Y-m-d") . ' ' . $timePattern->getStart()))->getTimestamp();
                    $endTS = (new \DateTime($pointer->format("Y-m-d") . ' ' . $timePattern->getEnd()))->getTimestamp();

                    try {
                        foreach ($this->timePeriodsManager->subtractTimePeriodsFromTimePeriod(
                            $startTS,
                            $endTS,
                            $this->consumingTime,
                            $this->dsDownTimes,
                            [$this, 'startGetter'],
                            [$this, 'endGetter'],
                        ) as $v) {
                            if (empty($v) || count($v) === 0) {
                                continue;
                            }

                            try {
                                foreach ($this->timePeriodsManager->subtractTimePeriodsFromTimePeriod(
                                    $v[0],
                                    $v[1],
                                    $this->consumingTime,
                                    $this->futureVisits,
                                    function (DSVisit $dsVisit): int {
                                        return $dsVisit->getVisitTimestamp();
                                    },
                                    function (DSVisit $dsVisit): int {
                                        return $dsVisit->getVisitTimestamp() + $dsVisit->getConsumingTime();
                                    }
                                ) as $v1) {
                                    if (empty($v1) || count($v1) === 0) {
                                        continue;
                                    }

                                    if ($v1[0] < $this->pointer->getTimestamp()) {
                                        continue;
                                    }

                                    return $v1[0];
                                }
                            } catch (InvalidConsumingTime $th) {
                                continue;
                            }
                        }
                    } catch (InvalidConsumingTime $th) {
                        continue;
                    }
                }

                do {
                    $pointer->modify("+1 day");
                } while (!isset($this->workSchedule[$pointer->format("l")]));
            }
        } finally {
            $this->futureVisits->setSort($this->oldSort);
        }
    }

    public function startGetter(object $value): int
    {
        return $value->getStartTimestamp();
    }

    public function endGetter(object $value): int
    {
        return $value->getEndTimestamp();
    }
}
