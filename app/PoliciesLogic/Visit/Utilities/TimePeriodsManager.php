<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;

class TimePeriodsManager
{
    private ValidateTimeRanges $validateTimeRanges;

    private null|array|\ArrayAccess|\Countable $parentTimePeriods;

    private null|array|\Closure $getParentTimePeriodStartTS;

    private null|array|\Closure $getParentTimePeriodEndTS;

    public function __construct(
        null|ValidateTimeRanges $validateTimeRanges = null,
        null|array|\ArrayAccess|\Countable $parentTimePeriods = null,
        null|array|\Closure $getParentTimePeriodEndTS = null,
        null|array|\Closure $getParentTimePeriodStartTS = null,
    ) {
        if (!is_null($parentTimePeriods) && (is_null($getParentTimePeriodStartTS) || is_null($getParentTimePeriodEndTS))) {
            throw new \InvalidArgumentException('The getParentTimePeriodStartTS and getParentTimePeriodEndTS variables are required when parentTimePeriods variable is not null.', 500);
        }

        if (!is_null($parentTimePeriods) && count($parentTimePeriods) === 0) {
            throw new \LogicException('The parentTimePeriod varaible is empty!', 500);
        }

        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
        $this->parentTimePeriods = $parentTimePeriods;
        $this->getParentTimePeriodStartTS = $getParentTimePeriodStartTS;
        $this->getParentTimePeriodEndTS = $getParentTimePeriodEndTS;
    }

    /**
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePeriods
     * @param array|\Closure $getTimePeriodStartTS
     * @param array|\Closure $getTimePeriodEndTS
     * @return \Generator<int, int[]>
     */
    public function subtractTimePeriodsFromParentTimePeriods(
        int $neededTime,
        array|\ArrayAccess|\Countable $timePeriods,
        array|\Closure $getTimePeriodStartTS,
        array|\Closure $getTimePeriodEndTS
    ): \Generator {
        if (!isset($this->parentTimePeriods)) {
            throw new \LogicException('There are no parent time periods available for this method!', 500);
        }

        $this->confirmIntReturnType($this->getParentTimePeriodEndTS);
        $this->confirmIntReturnType($this->getParentTimePeriodStartTS);

        for ($i = 0; $i < count($this->parentTimePeriods); $i++) {
            $parentTimePeriodStartTS = call_user_func($this->getParentTimePeriodStartTS, $this->parentTimePeriods[$i]);
            $parentTimePeriodEndTS = call_user_func($this->getParentTimePeriodEndTS, $this->parentTimePeriods[$i]);

            yield from $this->subtractTimePeriodsFromTimePeriod($parentTimePeriodStartTS, $parentTimePeriodEndTS, $neededTime, $timePeriods, $getTimePeriodStartTS, $getTimePeriodEndTS);
        }
    }

    /**
     * @param integer $startTS
     * @param integer $endTS
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePeriods
     * @param array|\Closure $getTimePeriodStartTS
     * @param array|\Closure $getTimePeriodEndTS
     * @return \Generator<int, int[]>
     */
    public function subtractTimePeriodsFromTimePeriod(
        int $startTS,
        int $endTS,
        int $neededTime,
        array|\ArrayAccess|\Countable $timePeriods,
        array|\Closure $getTimePeriodStartTS,
        array|\Closure $getTimePeriodEndTS
    ): \Generator {
        $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
        $this->confirmIntReturnType($getTimePeriodEndTS);
        $this->confirmIntReturnType($getTimePeriodStartTS);

        if (count($timePeriods) === 0) {
            return yield [$startTS, $endTS];
        }

        for ($i = 0; $i < count($timePeriods); $i++) {
            $timePeriodStartTS = call_user_func($getTimePeriodStartTS, $timePeriods[$i]);
            $timePeriodEndTS = call_user_func($getTimePeriodEndTS, $timePeriods[$i]);

            if ($timePeriodStartTS < $startTS && $timePeriodEndTS > $endTS) {
                return yield [];
            }

            if ($timePeriodEndTS <= $startTS) {
                continue;
            } elseif ($timePeriodStartTS <= $startTS) {
                $startTS = $timePeriodEndTS;
                try {
                    $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
                } catch (NeededTimeOutOfRange $th) {
                    return yield [];
                }
                continue;
            }

            if (isset($previousItemEndTS)) {
                $possibleStart = $previousItemEndTS;
            } else {
                $possibleStart = $startTS;
            }

            if ($timePeriodStartTS > $endTS) {
                $possibleEnd = $endTS;
            } else {
                $possibleEnd = $timePeriodStartTS;
            }

            if (
                $possibleStart >= $startTS &&
                $possibleEnd <= $endTS &&
                ($possibleEnd - $possibleStart) >= $neededTime
            ) {
                yield [$possibleStart, $possibleEnd];
            }

            $previousItemEndTS = $timePeriodEndTS;

            if ($timePeriodEndTS > $endTS) {
                break;
            }
        }

        if ($timePeriodEndTS < $endTS) {
            unset($possibleStart);
            if ($timePeriodEndTS >= $startTS && ($endTS - $timePeriodEndTS) >= $neededTime) {
                $possibleStart = $timePeriodEndTS;
            } elseif ($timePeriodEndTS <= $startTS) {
                $possibleStart = $startTS;
            }

            if (isset($possibleStart)) {
                yield [$possibleStart, $endTS];
            }
        }
    }

    /**
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePeriods
     * @param array|\Closure $getTimePeriodStartTS
     * @param array|\Closure $getTimePeriodEndTS
     * @return \Generator<int, int[]>
     */
    public function findIntersectionsOfTimePeriodsFromTimePeriods(
        int $neededTime,
        array|\ArrayAccess|\Countable $timePeriods,
        array|\Closure $getTimePeriodStartTS,
        array|\Closure $getTimePeriodEndTS
    ): \Generator {
        if (!isset($this->parentTimePeriods)) {
            throw new \LogicException('There are no parent time periods available for this method!', 500);
        }

        $this->confirmIntReturnType($this->getParentTimePeriodEndTS);
        $this->confirmIntReturnType($this->getParentTimePeriodStartTS);

        for ($i = 0; $i < count($this->parentTimePeriods); $i++) {
            $parentTimePeriodStartTS = call_user_func($this->getParentTimePeriodStartTS, $this->parentTimePeriods[$i]);
            $parentTimePeriodEndTS = call_user_func($this->getParentTimePeriodEndTS, $this->parentTimePeriods[$i]);

            yield from $this->findIntersectionsOfTimePeriodsFromTimePeriod($parentTimePeriodStartTS, $parentTimePeriodEndTS, $neededTime, $timePeriods, $getTimePeriodStartTS, $getTimePeriodEndTS);
        }
    }

    /**
     * @param integer $startTS
     * @param integer $endTS
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePeriods
     * @param array|\Closure $getTimePeriodStartTS
     * @param array|\Closure $getTimePeriodEndTS
     * @return \Generator<int, int[]>
     */
    public function findIntersectionsOfTimePeriodsFromTimePeriod(
        int $startTS,
        int $endTS,
        int $neededTime,
        array|\ArrayAccess|\Countable $timePeriods,
        array|\Closure $getTimePeriodStartTS,
        array|\Closure $getTimePeriodEndTS
    ): \Generator {
        $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
        $this->confirmIntReturnType($getTimePeriodEndTS);
        $this->confirmIntReturnType($getTimePeriodStartTS);

        if (count($timePeriods) === 0) {
            return yield [];
        }

        for ($i = 0; $i < count($timePeriods); $i++) {
            $timePeriodStartTS = call_user_func($getTimePeriodStartTS, $timePeriods[$i]);
            $timePeriodEndTS = call_user_func($getTimePeriodEndTS, $timePeriods[$i]);

            if ($timePeriodEndTS <= $startTS) {
                continue;
            }

            if ($timePeriodStartTS >= $endTS) {
                break;
            }

            if ($timePeriodStartTS <= $startTS) {
                $possibleStart = $startTS;
            } else {
                $possibleStart = $timePeriodStartTS;
            }

            if ($timePeriodEndTS >= $endTS) {
                $possibleEnd = $endTS;
            } else {
                $possibleEnd = $timePeriodEndTS;
            }

            if (
                $possibleStart >= $startTS &&
                $possibleEnd <= $endTS &&
                ($possibleEnd - $possibleStart) >= $neededTime
            ) {
                yield [$possibleStart, $possibleEnd];
            }
        }
    }

    private function confirmIntReturnType(array|\Closure $callback): void
    {
        if (is_array($callback)) {
            $type = (new \ReflectionMethod($callback[0], $callback[1]))->getReturnType();
        } else {
            $type = (new \ReflectionFunction($callback))->getReturnType();
        }

        if (
            ($type instanceof \ReflectionNamedType && $type->getName() !== 'int') ||
            ($type instanceof \ReflectionUnionType) ||
            ($type instanceof \ReflectionIntersectionType)
        ) {
            throw new \InvalidArgumentException('The provided callbacks must have integer as their return type.', 500);
        }
    }
}
