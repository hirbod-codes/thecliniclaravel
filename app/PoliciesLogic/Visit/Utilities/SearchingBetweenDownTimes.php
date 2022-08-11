<?php

namespace App\PoliciesLogic\Visit\Utilities;

use App\DataStructures\Time\DSDownTimes;
use App\DataStructures\Visit\DSVisits;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\PoliciesLogic\Exceptions\Visit\VisitSearchFailure;
use App\DataStructures\Time\DSDownTime;

class SearchingBetweenDownTimes
{
    private SearchingBetweenTimeRange $searchingBetweenTimeRange;

    private SearchBetweenTimestamps $searchBetweenTimestamps;

    private DownTime $downTime;

    private ValidateTimeRanges $validateTimeRanges;

    public function __construct(
        null|SearchBetweenTimestamps $searchBetweenTimestamps = null,
        null|SearchingBetweenTimeRange $searchingBetweenTimeRange = null,
        null|DownTime $downTime = null,
        null|ValidateTimeRanges $validateTimeRanges = null
    ) {
        $this->searchBetweenTimestamps = $searchBetweenTimestamps ?: new SearchBetweenTimestamps;
        $this->searchingBetweenTimeRange = $searchingBetweenTimeRange ?: new SearchingBetweenTimeRange;
        $this->downTime = $downTime ?: new DownTime;
        $this->validateTimeRanges = $validateTimeRanges ?: new ValidateTimeRanges;
    }

    public function search(int $firstTS, int $lastTS, DSVisits $futureVisits, DSDownTimes $dsDownTimes, int $consumingTime): int
    {
        $this->validateTimeRanges->checkConsumingTimeInTimeRange($firstTS, $lastTS, $consumingTime);

        $intruptingDSDownTimes = $this->downTime->findDownTimeIntruptionWithTimeRange($firstTS, $lastTS, $dsDownTimes);

        foreach ($this->searchBetweenTimestamps->search(
            $firstTS,
            $lastTS,
            $consumingTime,
            $intruptingDSDownTimes->cloneIt(),
            function (DSDownTime $dsDownTime): int {
                return $dsDownTime->getStartTimestamp();
            },
            function (DSDownTime $dsDownTime): int {
                return $dsDownTime->getEndTimestamp();
            }
        ) as $item) {
            if (!is_array($item)) {
                continue;
            }

            $previousBlock = $item[0];
            $currentBlock = $item[1];

            try {
                return $this->searchingBetweenTimeRange->search($previousBlock, $currentBlock, $consumingTime, $futureVisits);
            } catch (VisitSearchFailure $th) {
            } catch (NeededTimeOutOfRange $th) {
            }
        }

        throw new VisitSearchFailure("Failed to find a visit in the requested time range.", 500);
    }
}
