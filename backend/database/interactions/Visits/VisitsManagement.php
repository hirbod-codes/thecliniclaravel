<?php

namespace Database\Interactions\Visits;

use App\DataStructures\Time\DSDateTimePeriods;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\DataStructures\Visit\DSVisits;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\PoliciesLogic\Visit\CustomVisit;
use App\PoliciesLogic\Visit\FastestVisit;
use App\PoliciesLogic\Visit\IFindVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use Database\Interactions\Business\DataBaseRetrieveBusinessSettings;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveRegularVisits;

class VisitsManagement
{
    private null|IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders = null;

    private null|IDataBaseRetrieveLaserVisits $iDataBaseRetrieveLaserVisits = null;

    private null|IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings = null;

    public function __construct(
        null|IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders = null,
        null|IDataBaseRetrieveLaserVisits $iDataBaseRetrieveLaserVisits = null,
        null|IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders = null,
        null|IDataBaseRetrieveRegularVisits $iDataBaseRetrieveRegularVisits = null,
        null|IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings = null,
    ) {
        $this->iDataBaseRetrieveLaserOrders = $iDataBaseRetrieveLaserOrders ?: new DatabaseRetrieveLaserOrders;
        $this->iDataBaseRetrieveLaserVisits = $iDataBaseRetrieveLaserVisits ?: new DataBaseRetrieveLaserVisits;
        $this->iDataBaseRetrieveRegularOrders = $iDataBaseRetrieveRegularOrders ?: new DatabaseRetrieveRegularOrders;
        $this->iDataBaseRetrieveRegularVisits = $iDataBaseRetrieveRegularVisits ?: new DataBaseRetrieveRegularVisits;
        $this->iDataBaseRetrieveBusinessSettings = $iDataBaseRetrieveBusinessSettings ?: new DataBaseRetrieveBusinessSettings;
    }

    public function getLaserVisitFinder(int|LaserOrder $order, DSWeeklyTimePatterns|DSDateTimePeriods|null $userInput = null): IFindVisit
    {
        if (is_int($order)) {
            $order = $this->iDataBaseRetrieveLaserOrders->getLaserOrderById($order);
        }

        $futureVisits = $this->iDataBaseRetrieveLaserVisits->getDSFutureVisits();

        return $this->getVisitFinder($order->needed_time, $futureVisits, $userInput);
    }

    public function getRegularVisitFinder(int|RegularOrder $order, DSWeeklyTimePatterns|DSDateTimePeriods|null $userInput = null): IFindVisit
    {
        if (is_int($order)) {
            $order = $this->iDataBaseRetrieveRegularOrders->getRegularOrderById($order);
        }

        $futureVisits = $this->iDataBaseRetrieveRegularVisits->getDSFutureVisits();

        return $this->getVisitFinder($order->needed_time, $futureVisits, $userInput);
    }

    public function getVisitFinder(int $neededTime, DSVisits $dsVisits, DSWeeklyTimePatterns|DSDateTimePeriods|null $userInput = null): IFindVisit
    {
        $dsWoekSchedule = $this->iDataBaseRetrieveBusinessSettings->getWorkSchdule();
        $dsDownTimes = $this->iDataBaseRetrieveBusinessSettings->getDownTimes();

        if (is_null($userInput)) {
            $iFindVisit = new FastestVisit(
                new \DateTime,
                $neededTime,
                $dsVisits,
                $dsWoekSchedule,
                $dsDownTimes,
            );
        } elseif ($userInput instanceof DSWeeklyTimePatterns) {
            $iFindVisit = new WeeklyVisit(
                $userInput,
                $neededTime,
                $dsVisits,
                $dsWoekSchedule,
                $dsDownTimes,
                new \DateTime
            );
        } else {
            $iFindVisit = new CustomVisit(
                $userInput,
                $neededTime,
                $dsVisits,
                $dsWoekSchedule,
                $dsDownTimes
            );
        }

        return $iFindVisit;
    }
}
