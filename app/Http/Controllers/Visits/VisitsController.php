<?php

namespace App\Http\Controllers\Visits;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Models\BusinessDefault;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\DataBaseCreateLaserVisit;
use Database\Interactions\Visits\DataBaseCreateRegularVisit;
use Database\Interactions\Visits\DataBaseDeleteLaserVisit;
use Database\Interactions\Visits\DataBaseDeleteRegularVisit;
use Database\Interactions\Visits\DataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\DataBaseRetrieveRegularVisits;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TheClinic\Visit\FastestVisit;
use TheClinic\Visit\WeeklyVisit;
use TheClinicDataStructures\DataStructures\Time\DSWeekDaysPeriods;
use TheClinicDataStructures\DataStructures\Visit\Laser\DSLaserVisits;
use TheClinicDataStructures\DataStructures\Visit\Regular\DSRegularVisits;
use TheClinicUseCases\Visits\Creation\LaserVisitCreation;
use TheClinicUseCases\Visits\Creation\RegularVisitCreation;
use TheClinicUseCases\Visits\Deletion\LaserVisitDeletion;
use TheClinicUseCases\Visits\Deletion\RegularVisitDeletion;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseCreateRegularVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseDeleteRegularVisit;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use TheClinicUseCases\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use TheClinicUseCases\Visits\Retrieval\LaserVisitRetrieval;
use TheClinicUseCases\Visits\Retrieval\RegularVisitRetrieval;

class VisitsController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    private LaserVisitCreation $laserVisitCreation;
    private RegularVisitCreation $regularVisitCreation;
    private IDataBaseCreateLaserVisit $iDataBaseCreateLaserVisit;
    private IDataBaseCreateRegularVisit $iDataBaseCreateRegularVisit;

    private LaserVisitDeletion $laserVisitDeletion;
    private RegularVisitDeletion $regularVisitDeletion;
    private IDataBaseDeleteLaserVisit $iDataBaseDeleteLaserVisit;
    private IDataBaseDeleteRegularVisit $iDataBaseDeleteRegularVisit;

    private LaserVisitRetrieval $laserVisitRetrieval;
    private RegularVisitRetrieval $regularVisitRetrieval;
    private IDataBaseRetrieveLaserVisits $iDataBaseRetrieveLaserVisits;
    private IDataBaseRetrieveRegularVisits $iDataBaseRetrieveRegularVisits;

    public function __construct(
        null|CheckAuthentication $checkAuthentication = null,

        null|LaserVisitCreation $laserVisitCreation = null,
        null|RegularVisitCreation $regularVisitCreation = null,
        null|IDataBaseCreateLaserVisit $iDataBaseCreateLaserVisit = null,
        null|IDataBaseCreateRegularVisit $iDataBaseCreateRegularVisit = null,

        null|LaserVisitDeletion $laserVisitDeletion = null,
        null|RegularVisitDeletion $regularVisitDeletion = null,
        null|IDataBaseDeleteLaserVisit $iDataBaseDeleteLaserVisit = null,
        null|IDataBaseDeleteRegularVisit $iDataBaseDeleteRegularVisit = null,

        null|LaserVisitRetrieval $laserVisitRetrieval = null,
        null|RegularVisitRetrieval $regularVisitRetrieval = null,
        null|IDataBaseRetrieveLaserVisits $iDataBaseRetrieveLaserVisits = null,
        null|IDataBaseRetrieveRegularVisits $iDataBaseRetrieveRegularVisits = null
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;

        $this->laserVisitCreation = $laserVisitCreation ?: new LaserVisitCreation;
        $this->regularVisitCreation = $regularVisitCreation ?: new RegularVisitCreation;
        $this->iDataBaseCreateLaserVisit = $iDataBaseCreateLaserVisit ?: new DataBaseCreateLaserVisit;
        $this->iDataBaseCreateRegularVisit = $iDataBaseCreateRegularVisit ?: new DataBaseCreateRegularVisit;

        $this->laserVisitDeletion = $laserVisitDeletion ?: new LaserVisitDeletion;
        $this->regularVisitDeletion = $regularVisitDeletion ?: new RegularVisitDeletion;
        $this->iDataBaseDeleteLaserVisit = $iDataBaseDeleteLaserVisit ?: new DataBaseDeleteLaserVisit;
        $this->iDataBaseDeleteRegularVisit = $iDataBaseDeleteRegularVisit ?: new DataBaseDeleteRegularVisit;

        $this->laserVisitRetrieval = $laserVisitRetrieval ?: new LaserVisitRetrieval;
        $this->regularVisitRetrieval = $regularVisitRetrieval ?: new RegularVisitRetrieval;
        $this->iDataBaseRetrieveLaserVisits = $iDataBaseRetrieveLaserVisits ?: new DataBaseRetrieveLaserVisits;
        $this->iDataBaseRetrieveRegularVisits = $iDataBaseRetrieveRegularVisits ?: new DataBaseRetrieveRegularVisits;
    }

    public function laserIndex(int $accountId, string $sortByTimestamp, null|int $laserOrderId = null, null|int $timestamp = null, null|string $operator = null): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var User $targetUser */
        $targetUser = User::query()
            ->whereKey($accountId)
            ->first()
            //
        ;
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        $args = [$dsAuthenticated];

        if ($laserOrderId === null && $timestamp === null && $operator === null) {
            $method = 'User';
            $args[] = $dsTargetUser;
        } elseif ($laserOrderId !== null) {
            $found = false;
            foreach ($targetUser->orders as $order) {
                /** @var LaserOrder $laserOrder */
                if (($laserOrder = $order->laserOrder) !== null && $laserOrder->getKey() === $laserOrderId) {
                    $dsLaserOrder = $laserOrder->getDSLaserOrder();
                    $found = true;
                }
            }
            if (!$found) {
                throw new ModelNotFoundException('', 404);
            }

            $method = 'Order';
            $args[] = $dsTargetUser;
            $args[] = $dsLaserOrder;
        } elseif ($timestamp !== null && $operator !== null) {
            $method = 'Timestamp';
            $args[] = $operator;
            $args[] = $timestamp;
        }

        $args[] = $sortByTimestamp;
        $args[] = $this->iDataBaseRetrieveLaserVisits;

        /** @var DSLaserVisits $visits */
        $visits = $this->laserVisitRetrieval->{'getVisitsBy' . $method}(...$args);

        return response()->json($visits->toArray());
    }

    public function regularIndex(int $accountId, string $sortByTimestamp, null|int $regularOrderId = null, null|int $timestamp = null, null|string $operator = null): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var User $targetUser */
        $targetUser = User::query()
            ->whereKey($accountId)
            ->first()
            //
        ;
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        $args = [$dsAuthenticated];

        if ($regularOrderId === null && $timestamp === null && $operator === null) {
            $method = 'User';
            $args[] = $dsTargetUser;
        } elseif ($regularOrderId !== null) {
            $found = false;
            foreach ($targetUser->orders as $order) {
                /** @var RegularOrder $regularOrder */
                if (($regularOrder = $order->regularOrder) !== null && $regularOrder->getKey() === $regularOrderId) {
                    $dsRegularOrder = $regularOrder->getDSRegularOrder();
                    $found = true;
                }
            }
            if (!$found) {
                throw new ModelNotFoundException('', 404);
            }

            $method = 'Order';
            $args[] = $dsTargetUser;
            $args[] = $dsRegularOrder;
        } elseif ($timestamp !== null && $operator !== null) {
            $method = 'Timestamp';
            $args[] = $operator;
            $args[] = $timestamp;
        }

        $args[] = $sortByTimestamp;
        $args[] = $this->iDataBaseRetrieveRegularVisits;

        /** @var DSRegularVisits $visits */
        $visits = $this->regularVisitRetrieval->{'getVisitsBy' . $method}(...$args);

        return response()->json($visits->toArray());
    }

    public function laserStore(Request $request): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var LaserOrder $laserOrder */
        $laserOrder = LaserOrder::query()
            ->whereKey($request->laserOrderId)
            ->first();
        $dsLaserOrder = $laserOrder->getDSLaserOrder();

        /** @var User $targetUser */
        $targetUser = User::query()
            ->whereKey($request->targetUserId)
            ->first();
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        $now = new \DateTime();
        $futureVisits = LaserVisit::query()
            ->orderBy('visit_timestamp', 'asc')
            ->where('visit_timestamp', '>=', $now)
            ->get()
            ->all()
            //
        ;
        $futureVisits = LaserVisit::getDSLaserVisits($futureVisits, 'ASC');

        if ($request->weekDaysPeriods !== null) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($request->weekDaysPeriods),
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::first()->work_schedule,
                $dsDownTimes = BusinessDefault::first()->down_times,
                $startPoint = new \DateTime
            );
        } elseif ($request->dateTimePeriod !== null) {
            // $iFindVisit = new WeeklyVisit();
        } else {
            $iFindVisit = new FastestVisit(
                $startPoint = new \DateTime,
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::first()->work_schedule,
                $dsDownTimes = BusinessDefault::first()->down_times,
            );
        }

        $dsLaserOrder = $this->laserVisitCreation->create($dsLaserOrder, $dsTargetUser, $dsAuthenticated, $this->iDataBaseCreateLaserVisit, $iFindVisit);

        return response()->json($dsLaserOrder->toArray());
    }

    public function regularStore(Request $request): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var RegularOrder $regularOrder */
        $regularOrder = RegularOrder::query()
            ->whereKey($request->regularOrderId)
            ->first();
        $dsRegularOrder = $regularOrder->getDSRegularOrder();

        /** @var User $targetUser */
        $targetUser = User::query()
            ->whereKey($request->targetUserId)
            ->first();
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        $now = new \DateTime();
        $futureVisits = RegularVisit::query()
            ->orderBy('visit_timestamp', 'asc')
            ->where('visit_timestamp', '>=', $now)
            ->get()
            ->all()
            //
        ;
        $futureVisits = RegularVisit::getDSRegularVisits($futureVisits, 'ASC');

        if ($request->weekDaysPeriods !== null) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($request->weekDaysPeriods),
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::first()->work_schedule,
                $dsDownTimes = BusinessDefault::first()->down_times,
                $startPoint = new \DateTime
            );
        } elseif ($request->dateTimePeriod !== null) {
            // $iFindVisit = new WeeklyVisit();
        } else {
            $iFindVisit = new FastestVisit(
                $startPoint = new \DateTime,
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::first()->work_schedule,
                $dsDownTimes = BusinessDefault::first()->down_times,
            );
        }

        $dsRegularOrder = $this->regularVisitCreation->create($dsRegularOrder, $dsTargetUser, $dsAuthenticated, $this->iDataBaseCreateRegularVisit, $iFindVisit);

        return response()->json($dsRegularOrder->toArray());
    }

    public function laserShow(int $timestamp): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var DSLaserVisits $visits */
        $visits = $this->laserVisitRetrieval->getVisitsByTimestamp($dsAuthenticated, '=', $timestamp, 'desc', $this->iDataBaseRetrieveLaserVisits);

        return response()->json($visits[0]->toArray());
    }

    public function regularShow(int $timestamp): JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var DSRegularVisits $visits */
        $visits = $this->regularVisitRetrieval->getVisitsByTimestamp($dsAuthenticated, '=', $timestamp, 'desc', $this->iDataBaseRetrieveRegularVisits);

        return response()->json($visits[0]->toArray());
    }

    public function laserDestroy(int $laserVisitId, int $targetUserId): Response
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var User $targetUser */
        $targetUser = User::query()
            ->whereKey($targetUserId)
            ->first();
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        /** @var LaserVisit $laserVisit */
        $laserVisit = LaserVisit::query()
            ->whereKey($laserVisitId)
            ->first();
        $dsLaserVisit = $laserVisit->getDSLaserVisit();

        $this->laserVisitDeletion->delete($dsLaserVisit, $dsTargetUser, $dsAuthenticated, $this->iDataBaseDeleteLaserVisit);

        return response('', 200);
    }

    public function regularDestroy(int $regularVisitId, int $targetUserId): Response
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var User $targetUser */
        $targetUser = User::query()
            ->whereKey($targetUserId)
            ->first();
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        /** @var RegularVisit $regularVisit */
        $regularVisit = RegularVisit::query()
            ->whereKey($regularVisitId)
            ->first();
        $dsRegularVisit = $regularVisit->getDSRegularVisit();

        $this->regularVisitDeletion->delete($dsRegularVisit, $dsTargetUser, $dsAuthenticated, $this->iDataBaseDeleteRegularVisit);

        return response('', 200);
    }
}
