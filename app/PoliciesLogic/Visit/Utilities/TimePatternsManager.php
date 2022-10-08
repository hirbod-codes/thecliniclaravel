<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;

class TimePatternsManager
{
    private ValidateTimeRanges $validateTimeRanges;

    public function __construct(
        null|ValidateTimeRanges $validateTimeRanges = null,
    ) {
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
    }

    /**
     * @param string $start
     * @param string $end
     * @param integer $neededTime
     * @param array|\ArrayAccess|\Countable $timePatterns
     * @param array|\Closure $getTimePatternStart
     * @param array|\Closure $getTimePatternEnd
     * @return \Generator<int, string[]>
     */
    public function subtractTimePatternsFromTimePattern(
        string $start,
        string $end,
        int $neededTime,
        array|\ArrayAccess|\Countable $timePatterns,
        array|\Closure $getTimePatternStart,
        array|\Closure $getTimePatternEnd
    ): \Generator {
        $date = (new \DateTime())->format("Y-m-d");
        $startTS = (new \DateTime($date . ' ' . $start))->getTimestamp();
        $endTS = (new \DateTime($date . ' ' . $end))->getTimestamp();

        $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
        $this->confirmStringReturnType($getTimePatternEnd);
        $this->confirmStringReturnType($getTimePatternStart);

        if (count($timePatterns) === 0) {
            return yield [$start, $end];
        }

        for ($i = 0; $i < count($timePatterns); $i++) {
            $timePatternStart = call_user_func($getTimePatternStart, $timePatterns[$i]);
            $timePatternStartTS = (new \DateTime($date . ' ' . $timePatternStart))->getTimestamp();

            $timePatternEnd = call_user_func($getTimePatternEnd, $timePatterns[$i]);
            $timePatternEndTS = (new \DateTime($date . ' ' . $timePatternEnd))->getTimestamp();

            if ($timePatternStartTS < $startTS && $timePatternEndTS > $endTS) {
                return yield [];
            }

            if ($timePatternEndTS <= $startTS) {
                continue;
            } elseif ($timePatternStartTS <= $startTS) {
                $startTS = $timePatternEndTS;
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

            if ($timePatternStartTS > $endTS) {
                $possibleEnd = $endTS;
            } else {
                $possibleEnd = $timePatternStartTS;
            }

            if (
                $possibleStart >= $startTS &&
                $possibleEnd <= $endTS &&
                ($possibleEnd - $possibleStart) >= $neededTime
            ) {
                yield [(new \DateTime())->setTimestamp($possibleStart)->format("H:i:s"), (new \DateTime())->setTimestamp($possibleEnd)->format("H:i:s")];
            }

            $previousItemEndTS = $timePatternEndTS;

            if ($timePatternEndTS > $endTS) {
                break;
            }
        }

        if ($timePatternEndTS < $endTS) {
            unset($possibleStart);
            if ($timePatternEndTS >= $startTS && ($endTS - $timePatternEndTS) >= $neededTime) {
                $possibleStart = $timePatternEndTS;
            } elseif ($timePatternEndTS <= $startTS) {
                $possibleStart = $startTS;
            }

            if (isset($possibleStart)) {
                yield [(new \DateTime())->setTimestamp($possibleStart)->format("H:i:s"), (new \DateTime())->setTimestamp($endTS)->format("H:i:s")];
            }
        }
    }

    public function findIntersectionsOfTimePatternsFromTimePattern(
        string $start,
        string $end,
        int $neededTime,
        array|\ArrayAccess|\Countable $timePatterns,
        array|\Closure $getTimePatternStart,
        array|\Closure $getTimePatternEnd
    ): \Generator {
        $date = (new \DateTime())->format("Y-m-d");
        $startTS = (new \DateTime($date . ' ' . $start))->getTimestamp();
        $endTS = (new \DateTime($date . ' ' . $end))->getTimestamp();

        $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
        $this->confirmStringReturnType($getTimePatternStart);
        $this->confirmStringReturnType($getTimePatternEnd);

        if (count($timePatterns) === 0) {
            return yield [];
        }

        for ($i = 0; $i < count($timePatterns); $i++) {
            $timePatternStart = call_user_func($getTimePatternStart, $timePatterns[$i]);
            $timePatternStartTS = (new \DateTime($date . ' ' . $timePatternStart))->getTimestamp();
            $timePatternEnd = call_user_func($getTimePatternEnd, $timePatterns[$i]);
            $timePatternEndTS = (new \DateTime($date . ' ' . $timePatternEnd))->getTimestamp();

            if ($timePatternEndTS <= $startTS) {
                continue;
            }

            if ($timePatternStartTS >= $endTS) {
                break;
            }

            if ($timePatternStartTS <= $startTS) {
                $possibleStart = $startTS;
            } else {
                $possibleStart = $timePatternStartTS;
            }

            if ($timePatternEndTS >= $endTS) {
                $possibleEnd = $endTS;
            } else {
                $possibleEnd = $timePatternEndTS;
            }

            if (
                $possibleStart >= $startTS &&
                $possibleEnd <= $endTS &&
                ($possibleEnd - $possibleStart) >= $neededTime
            ) {
                yield [(new \DateTime())->setTimestamp($possibleStart)->format("H:i:s"), (new \DateTime())->setTimestamp($possibleEnd)->format("H:i:s")];
            }
        }
    }

    private function confirmStringReturnType(array|\Closure $callback): void
    {
        if (is_array($callback)) {
            $type = (new \ReflectionMethod($callback[0], $callback[1]))->getReturnType();
        } else {
            $type = (new \ReflectionFunction($callback))->getReturnType();
        }

        if (
            ($type instanceof \ReflectionNamedType && $type->getName() !== 'string') ||
            ($type instanceof \ReflectionUnionType) ||
            ($type instanceof \ReflectionIntersectionType)
        ) {
            throw new \InvalidArgumentException('The provided callbacks must have integer as their return type.', 500);
        }
    }
}
