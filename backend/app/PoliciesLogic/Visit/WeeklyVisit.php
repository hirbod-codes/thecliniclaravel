<?php

namespace App\PoliciesLogic\Visit;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSTimePattern;
use App\DataStructures\Time\DSTimePatterns;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Visit\DSVisit;
use App\DataStructures\Visit\DSVisits;
use App\PoliciesLogic\Exceptions\Visit\InvalidConsumingTime;
use App\PoliciesLogic\Exceptions\Visit\VisitTimeSearchFailure;
use App\PoliciesLogic\Visit\Utilities\TimePatternsManager;
use App\PoliciesLogic\Visit\Utilities\TimePeriodsManager;
use App\PoliciesLogic\Visit\Utilities\ValidateTimeRanges;

class WeeklyVisit implements IFindVisit
{
    private DSWeeklyTimePatterns|DSTimePatterns $dsTimePatterns;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWeeklyTimePatterns $workSchedule;

    private DSDownTimes $dsDownTimes;

    private \DateTime $pointer;

    private TimePeriodsManager $timePeriodsManager;
    private TimePatternsManager $timePatternsManager;

    private ValidateTimeRanges $validateTimeRanges;

    private string $oldSort;

    public function __construct(
        DSWeeklyTimePatterns|DSTimePatterns $dsTimePatterns,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWeeklyTimePatterns $workSchedule,
        DSDownTimes $dsDownTimes,
        null|\DateTime $pointer = null,
        null|TimePatternsManager $timePatternsManager = null,
        null|TimePeriodsManager $timePeriodsManager = null,
        null|ValidateTimeRanges $validateTimeRanges = null
    ) {
        $this->dsTimePatterns = $dsTimePatterns;
        $this->consumingTime = $consumingTime;

        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;

        $this->workSchedule = $workSchedule;
        $this->dsDownTimes = $dsDownTimes;

        $this->pointer = $pointer ?: new \DateTime;

        $this->timePeriodsManager = $timePeriodsManager ?: new TimePeriodsManager();
        $this->timePatternsManager = $timePatternsManager ?: new TimePatternsManager();

        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges();
    }

    public function getDSTimePatterns(): DSWeeklyTimePatterns|DSTimePatterns
    {
        return $this->dsTimePatterns;
    }

    public function patternStartGetter(object $value): string
    {
        return $value->getStart();
    }

    public function patternEndGetter(object $value): string
    {
        return $value->getEnd();
    }

    public function periodStartGetter(object $value): int
    {
        return $value->getStartTimestamp();
    }

    public function periodEndGetter(object $value): int
    {
        return $value->getEndTimestamp();
    }


    /**
     * @return integer
     * @throws NeededTimeOutOfRange if consuming time is bigger than all of the work schedule time patterns.
     * @throws VisitTimeSearchFailure if it's failing to find a visit time.
     */
    public function findVisit(): int
    {
        try {
            $this->validateTimeRanges->checkConsumingTimeInWorkSchedule($this->workSchedule, $this->consumingTime);
            $smallestTimestamp = null;
            $timestamps = [];

            foreach ($this->workSchedule as $weekDay => $timePatterns) {
                if ($this->dsTimePatterns instanceof DSWeeklyTimePatterns) {
                    if (isset($this->dsTimePatterns[$weekDay])) {
                        $dsTimePatterns = $this->dsTimePatterns[$weekDay];
                    } else {
                        continue;
                    }
                } else {
                    $dsTimePatterns = $this->dsTimePatterns;
                }

                try {
                    /** @var DSTimePattern $timePattern */
                    foreach ($timePatterns as $timePattern) {
                        foreach ($this->timePatternsManager->findIntersectionsOfTimePatternsFromTimePattern(
                            $timePattern->getStart(),
                            $timePattern->getEnd(),
                            $this->consumingTime,
                            $dsTimePatterns,
                            [$this, 'patternStartGetter'],
                            [$this, 'patternEndGetter'],
                        ) as $v) {
                            if (empty($v) || count($v) === 0) {
                                continue;
                            }

                            $pointer = $this->movePointerToClosestWeekDay((new \DateTime)->setTimestamp($this->pointer->getTimestamp()), $weekDay);

                            try {
                                while (1) {
                                    foreach ($this->timePeriodsManager->subtractTimePeriodsFromTimePeriod(
                                        (new \DateTime($pointer->format("Y-m-d") . ' ' . $v[0]))->getTimestamp(),
                                        (new \DateTime($pointer->format("Y-m-d") . ' ' . $v[1]))->getTimestamp(),
                                        $this->consumingTime,
                                        $this->dsDownTimes,
                                        [$this, 'periodStartGetter'],
                                        [$this, 'periodEndGetter'],
                                    ) as $v1) {
                                        if (empty($v1) || count($v1) === 0) {
                                            continue;
                                        }

                                        try {
                                            foreach ($this->timePeriodsManager->subtractTimePeriodsFromTimePeriod(
                                                $v1[0],
                                                $v1[1],
                                                $this->consumingTime,
                                                $this->futureVisits,
                                                function (DSVisit $dsVisit): int {
                                                    return $dsVisit->getVisitTimestamp();
                                                },
                                                function (DSVisit $dsVisit): int {
                                                    return $dsVisit->getVisitTimestamp() + $dsVisit->getConsumingTime();
                                                }
                                            ) as $v2) {
                                                if (empty($v2) || count($v2) === 0) {
                                                    continue;
                                                }
                                                $timestamps[] = $v2[0];
                                                if ($smallestTimestamp === null || $v2[0] < $smallestTimestamp) {
                                                    $smallestTimestamp = $v2[0];
                                                }
                                                goto after_while;
                                            }
                                        } catch (InvalidConsumingTime $th) {
                                            continue;
                                        }
                                    }

                                    $pointer = $this->movePointerToClosestWeekDay($pointer, $weekDay);
                                }
                            } catch (InvalidConsumingTime $th) {
                                continue;
                            }

                            after_while:
                        }
                    }
                } catch (InvalidConsumingTime $th) {
                    continue;
                }
            }

            if ($smallestTimestamp !== null) {
                return $smallestTimestamp;
            }

            throw new VisitTimeSearchFailure(trans_choice('Visits/visits.visit-not-found', 0), 404);
        } finally {
            $this->futureVisits->setSort($this->oldSort);
        }
    }

    private function movePointerToClosestWeekDay(\DateTime $pointer, string $weekDay): \DateTime
    {
        $pointer = (new \DateTime)->setTimestamp($pointer->getTimestamp());

        do {
            $pointer->modify("+1 day");
        } while ($pointer->format("l") !== $weekDay);

        return $pointer;
    }
}
