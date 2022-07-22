<?php

namespace App\Http\Controllers\Visits;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visits\LaserIndexRequest;
use App\Http\Requests\Visits\LaserShowAvailableRequest;
use App\Http\Requests\Visits\LaserStoreRequest;
use App\Http\Requests\Visits\RegularIndexRequest;
use App\Http\Requests\Visits\RegularShowAvailableRequest;
use App\Http\Requests\Visits\RegularStoreRequest;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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

    public function laserIndex(LaserIndexRequest $request): JsonResponse
    {
        $validateInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $args = [$dsAuthenticated];

        if (!isset($validateInput['laserOrderId']) && !isset($validateInput['timestamp']) && !isset($validateInput['operator'])) {
            /** @var User $targetUser */
            $targetUser = User::query()->whereKey($validateInput['accountId'])->firstOrFail();
            $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

            $method = 'User';
            $args[] = $dsTargetUser;
        } elseif (isset($validateInput['laserOrderId'])) {
            /** @var LaserOrder $laserOrder */
            $laserOrder = LaserOrder::query()->whereKey((int)$validateInput['laserOrderId'])->firstOrFail();
            $dsLaserOrder = $laserOrder->getDSLaserOrder();

            /** @var User $targetUser */
            $targetUser = $laserOrder->order->user;
            $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

            $method = 'Order';
            $args[] = $dsTargetUser;
            $args[] = $dsLaserOrder;
        } elseif (isset($validateInput['timestamp']) && isset($validateInput['operator'])) {
            $method = 'Timestamp';
            $args[] = $validateInput['operator'];
            $args[] = $validateInput['timestamp'];
        }

        $args[] = $validateInput['sortByTimestamp'];
        $args[] = $this->iDataBaseRetrieveLaserVisits;

        /** @var DSLaserVisits $visits */
        $visits = $this->laserVisitRetrieval->{'getVisitsBy' . $method}(...$args);

        return response()->json($visits->toArray());
    }

    public function regularIndex(RegularIndexRequest $request): JsonResponse
    {
        $validateInput = $request->safe()->all();
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        $args = [$dsAuthenticated];

        if (!isset($validateInput['regularOrderId']) && !isset($validateInput['timestamp']) && !isset($validateInput['operator'])) {
            /** @var User $targetUser */
            $targetUser = User::query()->whereKey($validateInput['accountId'])->firstOrFail();
            $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

            $method = 'User';
            $args[] = $dsTargetUser;
        } elseif (isset($validateInput['regularOrderId'])) {
            /** @var RegularOrder $regularOrder */
            $regularOrder = RegularOrder::query()->whereKey((int)$validateInput['regularOrderId'])->firstOrFail();
            $dsregularOrder = $regularOrder->getDSregularOrder();

            /** @var User $targetUser */
            $targetUser = $regularOrder->order->user;
            $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

            $method = 'Order';
            $args[] = $dsTargetUser;
            $args[] = $dsregularOrder;
        } elseif (isset($validateInput['timestamp']) && isset($validateInput['operator'])) {
            $method = 'Timestamp';
            $args[] = $validateInput['operator'];
            $args[] = $validateInput['timestamp'];
        }

        $args[] = $validateInput['sortByTimestamp'];
        $args[] = $this->iDataBaseRetrieveRegularVisits;

        /** @var DSRegularVisits $visits */
        $visits = $this->regularVisitRetrieval->{'getVisitsBy' . $method}(...$args);

        return response()->json($visits->toArray());
    }

    public function laserStore(LaserStoreRequest $request): JsonResponse|RedirectResponse
    {
        $validateInput = $request->safe()->all();

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var LaserOrder $laserOrder */
        $laserOrder = LaserOrder::query()
            ->whereKey($validateInput['laserOrderId'])
            ->firstOrFail();
        $dsLaserOrder = $laserOrder->getDSLaserOrder();

        /** @var User $targetUser */
        $targetUser = $laserOrder->order->user;
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

        if (isset($validateInput['weekDaysPeriods'])) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($validateInput['weekDaysPeriods']),
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
                $startPoint = new \DateTime
            );
        } elseif (isset($validateInput['dateTimePeriod'])) {
            // $iFindVisit = new WeeklyVisit();
        } else {
            $iFindVisit = new FastestVisit(
                $startPoint = new \DateTime,
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $dsLaserVisit = $this->laserVisitCreation->create($dsLaserOrder, $dsTargetUser, $dsAuthenticated, $this->iDataBaseCreateLaserVisit, $iFindVisit);

        return response()->json($dsLaserVisit->toArray());
    }

    public function regularStore(RegularStoreRequest $request): JsonResponse
    {
        $validateInput = $request->safe()->all();

        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var RegularOrder $regularOrder */
        $regularOrder = RegularOrder::query()
            ->whereKey($validateInput['regularOrderId'])
            ->firstOrFail();
        $dsRegularOrder = $regularOrder->getDSRegularOrder();

        /** @var User $targetUser */
        $targetUser = $regularOrder->order->user;
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

        if (isset($validateInput['weekDaysPeriods'])) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($validateInput['weekDaysPeriods']),
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
                $startPoint = new \DateTime
            );
        } elseif (isset($validateInput['dateTimePeriod'])) {
            // $iFindVisit = new WeeklyVisit();
        } else {
            $iFindVisit = new FastestVisit(
                $startPoint = new \DateTime,
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $dsRegularOrder = $this->regularVisitCreation->create($dsRegularOrder, $dsTargetUser, $dsAuthenticated, $this->iDataBaseCreateRegularVisit, $iFindVisit);

        return response()->json($dsRegularOrder->toArray());
    }

    public function laserShow(int $timestamp): Response|JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var DSLaserVisits $visits */
        $visits = $this->laserVisitRetrieval->getVisitsByTimestamp($dsAuthenticated, '=', $timestamp, 'desc', $this->iDataBaseRetrieveLaserVisits);

        if (count($visits) === 0) {
            return response(trans_choice('Visits/visits.visit-not-found', 0), 404);
        }

        return response()->json($visits[0]->toArray());
    }

    public function regularShow(int $timestamp): Response|JsonResponse
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var DSRegularVisits $visits */
        $visits = $this->regularVisitRetrieval->getVisitsByTimestamp($dsAuthenticated, '=', $timestamp, 'desc', $this->iDataBaseRetrieveRegularVisits);

        if (count($visits) === 0) {
            return response(trans_choice('Visits/visits.visit-not-found', 0), 404);
        }

        return response()->json($visits[0]->toArray());
    }

    public function laserShowAvailable(LaserShowAvailableRequest $request): JsonResponse|RedirectResponse
    {
        $validateInput = $request->safe()->all();

        /** @var LaserOrder $laserOrder */
        $laserOrder = LaserOrder::query()
            ->whereKey($validateInput['laserOrderId'])
            ->firstOrFail();
        $dsLaserOrder = $laserOrder->getDSLaserOrder();

        $now = new \DateTime();
        $futureVisits = LaserVisit::query()
            ->orderBy('visit_timestamp', 'asc')
            ->where('visit_timestamp', '>=', $now)
            ->get()
            ->all()
            //
        ;
        $futureVisits = LaserVisit::getDSLaserVisits($futureVisits, 'ASC');

        if (isset($validateInput['weekDaysPeriods'])) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($validateInput['weekDaysPeriods']),
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
                $startPoint = new \DateTime
            );
        } elseif (isset($validateInput['dateTimePeriod'])) {
            // $iFindVisit = new WeeklyVisit();
        } else {
            $iFindVisit = new FastestVisit(
                $startPoint = new \DateTime,
                $dsLaserOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $timestamp = $iFindVisit->findVisit();

        return response()->json(['availableVisitTimestamp' => $timestamp]);
    }

    public function regularShowAvailable(RegularShowAvailableRequest $request): JsonResponse|RedirectResponse
    {
        $validateInput = $request->safe()->all();

        /** @var RegularOrder $regularOrder */
        $regularOrder = RegularOrder::query()
            ->whereKey($validateInput['regularOrderId'])
            ->firstOrFail();
        $dsRegularOrder = $regularOrder->getDSRegularOrder();

        $now = new \DateTime();
        $futureVisits = RegularVisit::query()
            ->orderBy('visit_timestamp', 'asc')
            ->where('visit_timestamp', '>=', $now)
            ->get()
            ->all()
            //
        ;
        $futureVisits = RegularVisit::getDSRegularVisits($futureVisits, 'ASC');

        if (isset($validateInput['weekDaysPeriods'])) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($validateInput['weekDaysPeriods']),
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
                $startPoint = new \DateTime
            );
        } elseif (isset($validateInput['dateTimePeriod'])) {
            // $iFindVisit = new WeeklyVisit();
        } else {
            $iFindVisit = new FastestVisit(
                $startPoint = new \DateTime,
                $dsRegularOrder->getNeededTime(),
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $timestamp = $iFindVisit->findVisit();

        return response()->json(['availableVisitTimestamp' => $timestamp]);
    }

    public function laserDestroy(int $laserVisitId): Response
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var LaserVisit $laserVisit */
        $laserVisit = LaserVisit::query()
            ->whereKey($laserVisitId)
            ->firstOrFail();
        $dsLaserVisit = $laserVisit->getDSLaserVisit();

        /** @var User $targetUser */
        $targetUser = $laserVisit->laserOrder->order->user;
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        $this->laserVisitDeletion->delete($dsLaserVisit, $dsTargetUser, $dsAuthenticated, $this->iDataBaseDeleteLaserVisit);

        return response(trans_choice('Visits/visits.destroy', 0), 200);
    }

    public function regularDestroy(int $regularVisitId): Response
    {
        $dsAuthenticated = $this->checkAuthentication->getAuthenticatedDSUser();

        /** @var RegularVisit $regularVisit */
        $regularVisit = RegularVisit::query()
            ->whereKey($regularVisitId)
            ->firstOrFail();
        $dsRegularVisit = $regularVisit->getDSRegularVisit();

        /** @var User $targetUser */
        $targetUser = $regularVisit->regularOrder->order->user;
        $dsTargetUser = $targetUser->authenticatableRole()->getDataStructure();

        $this->regularVisitDeletion->delete($dsRegularVisit, $dsTargetUser, $dsAuthenticated, $this->iDataBaseDeleteRegularVisit);

        return response(trans_choice('Visits/visits.destroy', 0), 200);
    }
}
