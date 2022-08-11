<?php

namespace App\PoliciesLogic\Visit;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\PoliciesLogic\Exceptions\Visit\VisitSearchFailure;
use App\PoliciesLogic\Visit\Utilities\SearchingBetweenDownTimes;
use App\PoliciesLogic\Visit\Utilities\ValidateTimeRanges;
use App\DataStructures\Time\DSDateTimePeriod;
use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Time\DSWeekDaysPeriods;
use App\DataStructures\Time\DSWorkSchedule;
use App\DataStructures\Visit\DSVisits;

class WeeklyVisit implements IFindVisit
{
    private DSWeekDaysPeriods $dsWeekDaysPeriods;

    private int $consumingTime;

    private DSVisits $futureVisits;

    private DSWorkSchedule $dsWorkSchedule;

    private DSDownTimes $dsDownTimes;

    private \DateTime $startingPoint;

    private SearchingBetweenDownTimes $SearchingBetweenDownTimes;

    private ValidateTimeRanges $validateTimeRanges;

    private string $oldSort;

    public function __construct(
        DSWeekDaysPeriods $dsWeekDaysPeriods,
        int $consumingTime,
        DSVisits $futureVisits,
        DSWorkSchedule $dsWorkSchedule,
        DSDownTimes $dsDownTimes,
        null|\DateTime $startingPoint = null,
        null|SearchingBetweenDownTimes $SearchingBetweenDownTimes = null,
        null|ValidateTimeRanges $validateTimeRanges = null
    ) {
        $this->dsWeekDaysPeriods = $dsWeekDaysPeriods;
        $this->consumingTime = $consumingTime;

        $this->oldSort = $futureVisits->getSort();
        $futureVisits->setSort('ASC');
        $this->futureVisits = $futureVisits;

        $this->dsWorkSchedule = $dsWorkSchedule;
        $this->dsDownTimes = $dsDownTimes;

        $this->startingPoint = $startingPoint ?: new \DateTime;
        $this->SearchingBetweenDownTimes = $SearchingBetweenDownTimes ?: new SearchingBetweenDownTimes;
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
    }

    public function getDSWeekDaysPeriods(): DSWeekDaysPeriods
    {
        return $this->dsWeekDaysPeriods;
    }

    public function findVisit(): int
    {
        try {
            $found = false;
            $timestamps = [];

            foreach ($this->findIntersections($this->dsWeekDaysPeriods, $this->dsWorkSchedule, $this->consumingTime) as $previousBlock => $currentBlock) {
                if (!is_int($currentBlock)) {
                    continue;
                }

                try {
                    $this->validateTimeRanges->checkConsumingTimeInTimeRange($previousBlock, $currentBlock, $this->consumingTime);
                    $found = true;
                } catch (NeededTimeOutOfRange $ntor) {
                    continue;
                }

                $safety = 0;
                while ($safety < 500) {
                    try {
                        $timestamp = $this->SearchingBetweenDownTimes->search(
                            $previousBlock,
                            $currentBlock,
                            $this->futureVisits,
                            $this->dsDownTimes,
                            $this->consumingTime
                        );

                        if ($timestamp >= $this->startingPoint->getTimestamp()) {
                            break;
                        }
                    } catch (VisitSearchFailure $vsf) {
                    }

                    $previousBlock = (new \DateTime)->setTimestamp($previousBlock)->modify('+7 days')->getTimestamp();
                    $currentBlock = (new \DateTime)->setTimestamp($currentBlock)->modify('+7 days')->getTimestamp();
                    $safety++;
                }

                $timestamps[] = $timestamp;
            }

            if (!$found) {
                throw new NeededTimeOutOfRange();
            }

            return $this->findClosestTimestamp($timestamps);
        } finally {
            $this->futureVisits->setSort($this->oldSort);
        }
    }

    /**
     * @param integer[] $timestamps
     * @return integer
     */
    private function findClosestTimestamp(array $timestamps): int
    {
        if (empty($timestamps)) {
            throw new \LogicException('Failed to find a visit time.', 500);
        }

        $first = true;
        foreach ($timestamps as $timestamp) {
            if ($first) {
                $first = false;
                /** @var int $smallestTimestamp */
                $smallestTimestamp = $timestamp;
                continue;
            }

            if ($timestamp < $smallestTimestamp) {
                $smallestTimestamp = $timestamp;
            }
        }

        return $smallestTimestamp;
    }

    /**
     * @param DSWeekDaysPeriods $firstWDP
     * @param DSWeekDaysPeriods $secondWDP
     * @return \Generator<int, int>|\Generator<int, NeededTimeOutOfRange>
     */
    public function findIntersections(DSWeekDaysPeriods $firstWDP, DSWeekDaysPeriods $secondWDP): \Generator
    {
        /**
         * @var string $weekDay
         * @var DSDateTimePeriods $firstDTPs
         */
        foreach ($firstWDP as $weekDay => $firstDTPs) {
            yield from $this->walkDTPs($firstDTPs, $secondWDP[$weekDay], $weekDay);
        }
    }

    private function walkDTPs(DSDateTimePeriods $firstDTPs, DSDateTimePeriods $secondDTPs, string $weekDay): \Generator
    {
        $today = new \DateTime;
        $today->setTimestamp($this->startingPoint->getTimestamp());
        $this->moveToWeekDay($today, $weekDay);

        /** @var DSDateTimePeriod $firstDTP */
        foreach ($firstDTPs as $firstDTP) {
            $firstDTP->setDate($today);
            /** @var DSDateTimePeriod $secondDTP */
            foreach ($secondDTPs as $secondDTP) {
                $secondDTP->setDate($today);
                yield from $this->compareTwoDTP($firstDTP, $secondDTP);
            }
        }
    }

    private function moveToWeekDay(\DateTime &$today, string $weekDay): void
    {
        if (!in_array($weekDay, DSWeekDaysPeriods::$weekDays)) {
            throw new \InvalidArgumentException('Wrong name for a day of a week.The given name: ' . $weekDay, 500);
        }

        while ($today->format('l') !== $weekDay) {
            $today->modify('+1 day');
        }
    }

    private function compareTwoDTP(DSDateTimePeriod $firstDTP, DSDateTimePeriod $secondDTP): \Generator
    {
        if (
            ($firstDTP->getStartTimestamp() < $secondDTP->getStartTimestamp() &&
                $firstDTP->getEndTimestamp() <= $secondDTP->getStartTimestamp())
            ||
            ($firstDTP->getStartTimestamp() >= $secondDTP->getEndTimestamp() &&
                $firstDTP->getEndTimestamp() > $secondDTP->getEndTimestamp())
        ) {
            return yield new NeededTimeOutOfRange("", 1);
        }

        if ($firstDTP->getStartTimestamp() > $secondDTP->getStartTimestamp()) {
            $previousBlock = $firstDTP->getStartTimestamp();
        } else {
            $previousBlock = $secondDTP->getStartTimestamp();
        }

        if ($firstDTP->getEndTimestamp() < $secondDTP->getEndTimestamp()) {
            $currentBlock = $firstDTP->getEndTimestamp();
        } else {
            $currentBlock = $secondDTP->getEndTimestamp();
        }

        yield $previousBlock => $currentBlock;
    }
}
