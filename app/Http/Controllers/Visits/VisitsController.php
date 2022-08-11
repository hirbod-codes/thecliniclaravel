<?php

namespace App\Http\Controllers\Visits;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visits\LaserShowAvailableRequest;
use App\Http\Requests\Visits\LaserStoreRequest;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\PoliciesLogic\Visit\FastestVisit;
use App\PoliciesLogic\Visit\WeeklyVisit;
use App\DataStructures\Time\DSWeekDaysPeriods;
use App\Http\Requests\Visits\IndexRequest;
use App\Http\Requests\Visits\LaserDestroyRequest;
use App\Http\Requests\Visits\RegularDestroyRequest;
use App\Http\Requests\Visits\VisitsCountRequest;
use App\UseCases\Visits\Creation\LaserVisitCreation;
use App\UseCases\Visits\Creation\RegularVisitCreation;
use App\UseCases\Visits\Deletion\LaserVisitDeletion;
use App\UseCases\Visits\Deletion\RegularVisitDeletion;
use App\UseCases\Visits\Interfaces\IDataBaseCreateLaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseCreateRegularVisit;
use App\UseCases\Visits\Interfaces\IDataBaseDeleteLaserVisit;
use App\UseCases\Visits\Interfaces\IDataBaseDeleteRegularVisit;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use App\UseCases\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use App\UseCases\Visits\Retrieval\LaserVisitRetrieval;
use App\UseCases\Visits\Retrieval\RegularVisitRetrieval;
use Illuminate\Database\Eloquent\Builder;

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

    public function index(IndexRequest $request): JsonResponse
    {
        $validateInput = $request->safe()->all();

        if (isset($validateInput['accountId'])) {
            /** @var User $targetUser */
            $targetUser = User::query()->whereKey($validateInput['accountId'])->firstOrFail();

            $method = 'User';
            $args['targetUser'] = $targetUser;
        } elseif (isset($validateInput['orderId'])) {
            switch ($validateInput['businessName']) {
                case 'laser':
                    /** @var LaserOrder $order */
                    $order = LaserOrder::query()->whereKey((int)$validateInput['orderId'])->firstOrFail();
                    break;

                case 'regular':
                    /** @var RegularOrder $order */
                    $order = RegularOrder::query()->whereKey((int)$validateInput['orderId'])->firstOrFail();
                    break;

                default:
                    throw new \LogicException('!!!!', 500);
                    break;
            }

            $method = 'Order';
            $args[$validateInput['businessName'] . 'Order'] = $order;
        } elseif (isset($validateInput['timestamp']) && isset($validateInput['operator'])) {
            $method = 'Timestamp';
            $args['roleName'] = $validateInput['roleName'];
            $args['operator'] = $validateInput['operator'];
            $args['timestamp'] = $validateInput['timestamp'];
            $args['count'] = $validateInput['count'];
            if (isset($validateInput['lastVisitTimestamp'])) {
                $args['lastVisitTimestamp'] = $validateInput['lastVisitTimestamp'];
            }
        }

        $args['sortByTimestamp'] = $validateInput['sortByTimestamp'];
        $args['db'] = $this->{'iDataBaseRetrieve' . ucfirst($validateInput['businessName']) . 'Visits'};

        /** @var array $visits */
        $visits = $this->{$validateInput['businessName'] . 'VisitRetrieval'}->{'getVisitsBy' . $method}(...$args);

        return response()->json($visits);
    }

    public function visitsCount(VisitsCountRequest $request): Response
    {
        $input = $request->safe()->all();
        $roleName = $input['roleName'];
        $user = (new CheckAuthentication)->getAuthenticated();
        $userRoleName = $user->authenticatableRole->role->roleName->name;
        $canReadSelf = false;
        foreach ($user->authenticatableRole->role->role->retrieveVisitSubjects as $retrieveVisit) {
            if ($retrieveVisit->object !== null) {
                continue;
            }
            $canReadSelf = true;
            break;
        }
        $isSelf = $userRoleName === $roleName;

        switch ($input['businessName']) {
            case 'laser':
                $query = LaserVisit::query();
                break;

            case 'regular':
                $query = RegularVisit::query();
                break;

            default:
                throw new \LogicException('!!!!', 500);
                break;
        }

        $count = $query
            ->whereHas($input['businessName'] . 'Order', function (Builder $query) use ($roleName, $user, $isSelf, $canReadSelf) {
                $query->whereHas('order', function (Builder $query) use ($roleName, $user, $isSelf, $canReadSelf) {
                    $query->whereHas('user', function (Builder $query) use ($roleName, $user, $isSelf, $canReadSelf) {
                        if ($isSelf && !$canReadSelf) {
                            $query->whereKeyNot($user->getKey());
                        }
                        $i = 0;
                        foreach ((new User)->getChildrenTypesRelationNames() as $relation) {
                            if ($i === 0) {
                                $query->whereHas($relation, function (Builder $query) use ($roleName) {
                                    $query->whereHas('role', function (Builder $query) use ($roleName) {
                                        $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                            $query->where('name', '=', $roleName);
                                        });
                                    });
                                });
                            } else {
                                $query->orWhereHas($relation, function (Builder $query) use ($roleName) {
                                    $query->whereHas('role', function (Builder $query) use ($roleName) {
                                        $query->whereHas('roleName', function (Builder $query) use ($roleName) {
                                            $query->where('name', '=', $roleName);
                                        });
                                    });
                                });
                            }
                            $i++;
                        }
                    });
                });
            })
            ->count()
            //
        ;

        return response($count);
    }

    public function laserStore(LaserStoreRequest $request): JsonResponse|RedirectResponse
    {
        $validateInput = $request->safe()->all();

        /** @var LaserOrder $laserOrder */
        $laserOrder = LaserOrder::query()
            ->whereKey($validateInput['laserOrderId'])
            ->firstOrFail();

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
                $laserOrder->needed_time,
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
                $laserOrder->needed_time,
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $laserVisit = $this->laserVisitCreation->create($laserOrder, $this->iDataBaseCreateLaserVisit, $iFindVisit);

        return response()->json($laserVisit->toArray());
    }

    public function regularStore(RegularStoreRequest $request): JsonResponse
    {
        $validateInput = $request->safe()->all();

        /** @var RegularOrder $regularOrder */
        $regularOrder = RegularOrder::query()
            ->whereKey($validateInput['regularOrderId'])
            ->firstOrFail();

        $now = new \DateTime();
        $futureVisits = RegularVisit::query()
            ->orderBy('visit_timestamp', 'asc')
            ->where('visit_timestamp', '>=', $now)
            ->get()
            ->all()
            //
        ;
        $futureVisits = RegularVisit::getDSregularVisits($futureVisits, 'ASC');

        if (isset($validateInput['weekDaysPeriods'])) {
            $iFindVisit = new WeeklyVisit(
                $dsWeekDaysPeriods = DSWeekDaysPeriods::toObject($validateInput['weekDaysPeriods']),
                $regularOrder->needed_time,
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
                $regularOrder->needed_time,
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $regularVisit = $this->regularVisitCreation->create($regularOrder, $this->iDataBaseCreateRegularVisit, $iFindVisit);

        return response()->json($regularVisit->toArray());
    }

    public function laserShowAvailable(LaserShowAvailableRequest $request): JsonResponse|RedirectResponse
    {
        $validateInput = $request->safe()->all();

        /** @var LaserOrder $laserOrder */
        $laserOrder = LaserOrder::query()
            ->whereKey($validateInput['laserOrderId'])
            ->firstOrFail();

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
                $laserOrder->needed_time,
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
                $laserOrder->needed_time,
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
                $regularOrder->needed_time,
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
                $regularOrder->needed_time,
                $futureVisits,
                $dsWoekSchedule = BusinessDefault::firstOrFail()->work_schedule,
                $dsDownTimes = BusinessDefault::firstOrFail()->down_times,
            );
        }

        $timestamp = $iFindVisit->findVisit();

        return response()->json(['availableVisitTimestamp' => $timestamp]);
    }

    public function laserDestroy(LaserDestroyRequest $request,): Response
    {
        $input = $request->safe()->all();

        /** @var LaserVisit $laserVisit */
        $laserVisit = LaserVisit::query()
            ->whereKey($input['laserOrderId'])
            ->firstOrFail();

        $this->laserVisitDeletion->delete($laserVisit, $this->iDataBaseDeleteLaserVisit);

        return response(trans_choice('Visits/visits.destroy', 0), 200);
    }

    public function regularDestroy(RegularDestroyRequest $request): Response
    {
        $input = $request->safe()->all();

        /** @var RegularVisit $regularVisit */
        $regularVisit = RegularVisit::query()
            ->whereKey($input['regularOrderId'])
            ->firstOrFail();

        $this->regularVisitDeletion->delete($regularVisit, $this->iDataBaseDeleteRegularVisit);

        return response(trans_choice('Visits/visits.destroy', 0), 200);
    }
}
