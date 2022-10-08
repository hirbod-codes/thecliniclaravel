<?php

namespace App\PoliciesLogic\Visit;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Time\Util\TimeDSConverter;
use App\DataStructures\Visit\DSVisit;
use App\DataStructures\Visit\DSVisits;
use App\PoliciesLogic\Visit\Utilities\TimePeriodsManager;

class CustomVisit implements IFindVisit
{
    private DSDateTimePeriods $dsDateTimePeriods;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWeeklyTimePatterns $workSchedule;

    private DSDownTimes $dsDownTimes;

    private TimePeriodsManager $timePeriodsManager;

    private TimeDSConverter $timeDSConverter;

    private string $oldSort;

    public function __construct(
        DSDateTimePeriods $dsDateTimePeriods,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWeeklyTimePatterns $workSchedule,
        DSDownTimes $dsDownTimes,
        null|TimeDSConverter $timeDSConverter = null,
        null|TimePeriodsManager $timePeriodsManager = null,
    ) {
        $this->dsDateTimePeriods = $dsDateTimePeriods;
        $this->consumingTime = $consumingTime;

        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;

        $this->workSchedule = $workSchedule;
        $this->dsDownTimes = $dsDownTimes;

        $this->timeDSConverter = $timeDSConverter ?: new TimeDSConverter;
        $this->timePeriodsManager = $timePeriodsManager ?: new TimePeriodsManager(null, $dsDateTimePeriods, [$this, 'startGetter'], [$this, 'endGetter']);
    }

    public function startGetter(object $value): int
    {
        return $value->getStartTimestamp();
    }

    public function endGetter(object $value): int
    {
        return $value->getEndTimestamp();
    }

    public function findVisit(): int
    {
        try {
            $smallestTimesstamp = null;
            $timestamps = [];

            foreach ($this->timePeriodsManager->findIntersectionsOfTimePeriodsFromParentTimePeriods(
                $this->consumingTime,
                $this->timeDSConverter->ConvertTimePatternToTimePeriods($this->workSchedule, $this->dsDateTimePeriods),
                [$this, 'startGetter'],
                [$this, 'endGetter']
            ) as $key => $v) {
                // fwrite(STDOUT, "\033[33mv => " . json_encode([(new \DateTime)->setTimestamp($v[0])->format("Y-m-d H:i:s l"), (new \DateTime)->setTimestamp($v[1])->format("Y-m-d H:i:s l")], JSON_PRETTY_PRINT) . "\033\n");
                if (empty($v) || count($v) === 0) {
                    continue;
                }

                foreach ($this->timePeriodsManager->subtractTimePeriodsFromTimePeriod(
                    $v[0],
                    $v[1],
                    $this->consumingTime,
                    $this->dsDownTimes,
                    [$this, 'startGetter'],
                    [$this, 'endGetter']
                ) as $key => $v1) {
                    // fwrite(STDOUT, "\033[33mv1 => " . json_encode([(new \DateTime)->setTimestamp($v1[0])->format("Y-m-d H:i:s l"), (new \DateTime)->setTimestamp($v1[1])->format("Y-m-d H:i:s l")], JSON_PRETTY_PRINT) . "\033\n");
                    if (empty($v1) || count($v1) === 0) {
                        continue;
                    }

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
                    ) as $key => $v2) {
                        // fwrite(STDOUT, "\033[33mv2 => " . json_encode([(new \DateTime)->setTimestamp($v2[0])->format("Y-m-d H:i:s l"), (new \DateTime)->setTimestamp($v2[1])->format("Y-m-d H:i:s l")], JSON_PRETTY_PRINT) . "\033\n");
                        if (empty($v2) || count($v2) === 0) {
                            continue;
                        }
                        return $v2[0];
                        $timestamps[] = $v2[0];
                        if ($smallestTimesstamp === null || $v2[0] < $smallestTimesstamp) {
                            $smallestTimesstamp = $v2[0];
                        }
                    }
                }
            }

            if ($smallestTimesstamp === null) {
                throw new NeededTimeOutOfRange();
            }

            return $smallestTimesstamp;
        } finally {
            $this->futureVisits->setSort($this->oldSort);
        }
    }
}
