<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;

class SearchBetweenTimestamps
{
    private ValidateTimeRanges $validateTimeRanges;

    public function __construct(null|ValidateTimeRanges $validateTimeRanges = null)
    {
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
    }

    /**
     * @param integer $startTS
     * @param integer $endTS
     * @param integer $neededTime
     * @param \ArrayAccess|\Countable $arrayAccess
     * @param array|\Closure $getItemStartTS
     * @param array|\Closure $getItemEndTS
     * @return \Generator<int, int[]>|\Generator<int, NeededTimeOutOfRange>
     */
    public function search(int $startTS, int $endTS, int $neededTime, \ArrayAccess|\Countable $arrayAccess, array|\Closure $getItemStartTS, array|\Closure $getItemEndTS): \Generator
    {
        $this->validateArrayAccess($arrayAccess);
        $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
        $this->confirmIntReturnType($getItemEndTS);
        $this->confirmIntReturnType($getItemStartTS);

        if (count($arrayAccess) === 0) {
            return yield [$startTS, $endTS];
        }

        for ($i = 0; $i < count($arrayAccess); $i++) {
            (new \ReflectionFunction($getItemStartTS))->getReturnType();
            $itemStartTS = call_user_func($getItemStartTS, $arrayAccess[$i]);
            $itemEndTS = call_user_func($getItemEndTS, $arrayAccess[$i]);

            if ($itemStartTS < $startTS && $itemEndTS > $endTS) {
                return yield new NeededTimeOutOfRange('', 500);
            }

            if ($itemEndTS <= $startTS) {
                continue;
            } elseif ($itemStartTS <= $startTS) {
                $startTS = $itemEndTS;
                try {
                    $this->validateTimeRanges->checkConsumingTimeInTimeRange($startTS, $endTS, $neededTime);
                } catch (NeededTimeOutOfRange $th) {
                    return yield $th;
                }
                continue;
            }

            if (isset($previousItemEndTS)) {
                $previousBlock = $previousItemEndTS;
            } else {
                $previousBlock = $startTS;
            }

            if ($itemStartTS > $endTS) {
                $currentBlock = $endTS;
            } else {
                $currentBlock = $itemStartTS;
            }

            if (
                $previousBlock >= $startTS &&
                $currentBlock <= $endTS &&
                ($currentBlock - $previousBlock) >= $neededTime
            ) {
                yield [$previousBlock, $currentBlock];
            }

            $previousItemEndTS = $itemEndTS;

            if ($itemEndTS > $endTS) {
                break;
            }
        }

        if ($itemEndTS < $endTS) {
            unset($previousBlock);
            if ($itemEndTS >= $startTS && ($endTS - $itemEndTS) >= $neededTime) {
                $previousBlock = $itemEndTS;
            } elseif ($itemEndTS <= $startTS) {
                $previousBlock = $startTS;
            }

            if (isset($previousBlock)) {
                yield [$previousBlock, $endTS];
            }
        }
    }

    private function validateArrayAccess(\ArrayAccess|\Countable $arrayAccess): void
    {
        if (
            !($arrayAccess instanceof \ArrayAccess) ||
            !($arrayAccess instanceof \Countable)
        ) {
            throw new \InvalidArgumentException('The varable $arrayAccess doesn\'t implement required interfaces.', 500);
        }
    }

    private function confirmIntReturnType(array|\Closure $callback): void
    {
        $type = (new \ReflectionFunction($callback))->getReturnType();

        if (
            ($type instanceof \ReflectionNamedType && $type->getName() !== 'int') ||
            ($type instanceof \ReflectionUnionType) ||
            ($type instanceof \ReflectionIntersectionType)
        ) {
            throw new \InvalidArgumentException('The provided callbacks must have integer as their return type.', 500);
        }
    }
}
