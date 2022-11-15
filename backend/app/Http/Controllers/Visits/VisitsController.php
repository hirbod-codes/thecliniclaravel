<?php

namespace App\Http\Controllers\Visits;

use App\Auth\CheckAuthentication;
use App\DataStructures\Time\DSDateTimePeriods;
use App\Http\Controllers\Controller;
use App\Http\Requests\Visits\LaserShowAvailableRequest;
use App\Http\Requests\Visits\LaserStoreRequest;
use App\Http\Requests\Visits\RegularShowAvailableRequest;
use App\Http\Requests\Visits\RegularStoreRequest;
use App\Models\User;
use App\Models\Visit\LaserVisit;
use App\Models\Visit\RegularVisit;
use Database\Interactions\Visits\Creation\DataBaseCreateLaserVisit;
use Database\Interactions\Visits\Creation\DataBaseCreateRegularVisit;
use Database\Interactions\Visits\Deletion\DataBaseDeleteLaserVisit;
use Database\Interactions\Visits\Deletion\DataBaseDeleteRegularVisit;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\Retrieval\DataBaseRetrieveRegularVisits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\DataStructures\Time\DSWeeklyTimePatterns;
use App\Http\Requests\Visits\IndexRequest;
use App\Http\Requests\Visits\LaserDestroyRequest;
use App\Http\Requests\Visits\RegularDestroyRequest;
use App\Http\Requests\Visits\VisitsCountRequest;
use App\PoliciesLogic\Exceptions\Visit\NeededTimeOutOfRange;
use App\PoliciesLogic\Exceptions\Visit\VisitTimeSearchFailure;
use Database\Interactions\Accounts\AccountsManagement;
use Database\Interactions\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use Database\Interactions\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Database\Interactions\Visits\Interfaces\IDataBaseCreateLaserVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseCreateRegularVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseDeleteLaserVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseDeleteRegularVisit;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveLaserVisits;
use Database\Interactions\Visits\Interfaces\IDataBaseRetrieveRegularVisits;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Database\Interactions\Business\DataBaseRetrieveBusinessSettings;
use Database\Interactions\Business\Interfaces\IDataBaseRetrieveBusinessSettings;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Database\Interactions\Visits\VisitsManagement;
use Illuminate\Database\Eloquent\Builder;

class VisitsController extends Controller
{
    private CheckAuthentication $checkAuthentication;
    private AccountsManagement $accountsManagement;
    private VisitsManagement $visitsManagement;

    private IDataBaseCreateLaserVisit $iDataBaseCreateLaserVisit;
    private IDataBaseCreateRegularVisit $iDataBaseCreateRegularVisit;

    private IDataBaseDeleteLaserVisit $iDataBaseDeleteLaserVisit;
    private IDataBaseDeleteRegularVisit $iDataBaseDeleteRegularVisit;

    private IDataBaseRetrieveLaserVisits $iDataBaseRetrieveLaserVisits;
    private IDataBaseRetrieveRegularVisits $iDataBaseRetrieveRegularVisits;

    private IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders;
    private IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders;

    private IDataBaseRetrieveAccounts $iDataBaseRetrieveAccounts;

    private IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings;

    public function __construct(
        null|CheckAuthentication $checkAuthentication = null,
        null|AccountsManagement $accountsManagement = null,
        null|IDataBaseCreateLaserVisit $iDataBaseCreateLaserVisit = null,
        null|IDataBaseCreateRegularVisit $iDataBaseCreateRegularVisit = null,
        null|IDataBaseDeleteLaserVisit $iDataBaseDeleteLaserVisit = null,
        null|IDataBaseDeleteRegularVisit $iDataBaseDeleteRegularVisit = null,
        null|IDataBaseRetrieveLaserVisits $iDataBaseRetrieveLaserVisits = null,
        null|IDataBaseRetrieveRegularVisits $iDataBaseRetrieveRegularVisits = null,
        null|IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders = null,
        null|IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders = null,
        null|IDataBaseRetrieveAccounts $iDataBaseRetrieveAccounts = null,
        null|IDataBaseRetrieveBusinessSettings $iDataBaseRetrieveBusinessSettings = null,
        null|VisitsManagement $visitsManagement = null
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->accountsManagement = $accountsManagement ?: new AccountsManagement;

        $this->iDataBaseCreateLaserVisit = $iDataBaseCreateLaserVisit ?: new DataBaseCreateLaserVisit;
        $this->iDataBaseCreateRegularVisit = $iDataBaseCreateRegularVisit ?: new DataBaseCreateRegularVisit;

        $this->iDataBaseDeleteLaserVisit = $iDataBaseDeleteLaserVisit ?: new DataBaseDeleteLaserVisit;
        $this->iDataBaseDeleteRegularVisit = $iDataBaseDeleteRegularVisit ?: new DataBaseDeleteRegularVisit;

        $this->iDataBaseRetrieveLaserVisits = $iDataBaseRetrieveLaserVisits ?: new DataBaseRetrieveLaserVisits;
        $this->iDataBaseRetrieveRegularVisits = $iDataBaseRetrieveRegularVisits ?: new DataBaseRetrieveRegularVisits;

        $this->iDataBaseRetrieveLaserOrders = $iDataBaseRetrieveLaserOrders ?: new DatabaseRetrieveLaserOrders;
        $this->iDataBaseRetrieveRegularOrders = $iDataBaseRetrieveRegularOrders ?: new DatabaseRetrieveRegularOrders;

        $this->iDataBaseRetrieveAccounts = $iDataBaseRetrieveAccounts ?: new DataBaseRetrieveAccounts;

        $this->iDataBaseRetrieveBusinessSettings = $iDataBaseRetrieveBusinessSettings ?: new DataBaseRetrieveBusinessSettings;

        $this->visitsManagement = $visitsManagement ?: new VisitsManagement(
            $this->iDataBaseRetrieveLaserOrders,
            $this->iDataBaseRetrieveLaserVisits,
            $this->iDataBaseRetrieveRegularOrders,
            $this->iDataBaseRetrieveRegularVisits,
            $this->iDataBaseRetrieveBusinessSettings,
        );
    }

    public function index(IndexRequest $request): JsonResponse|Response
    {
        $validatedInput = $request->safe()->all();

        switch (strtolower($validatedInput['businessName'])) {
            case 'laser':
                unset($validatedInput['businessName']);
                return $this->laserIndex($validatedInput);
                break;

            case 'regular':
                unset($validatedInput['businessName']);
                return $this->regularIndex($validatedInput);
                break;

            default:
                return response('There\'s no such business name: ' . strval($validatedInput['businessName']), 404);
                break;
        }
    }

    private function laserIndex(array $input): JsonResponse
    {
        $method = 'getVisits';
        if (isset($input['accountId'])) {
            $method .= 'ByUser';

            $targetUser = $this->iDataBaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername((int)$input['accountId']));
            $args['targetUser'] = $targetUser;
        } elseif (isset($input['orderId'])) {
            $method .= 'ByOrder';

            $order = $this->iDataBaseRetrieveLaserOrders->getLaserOrderById((int)$input['orderId']);
            $args['laserOrder'] = $order;
        } elseif (isset($input['timestamp']) && isset($input['operator'])) {
            $method .= 'ByTimestamp';
            $args['roleName'] = $input['roleName'];
            $args['operator'] = $input['operator'];
            $args['timestamp'] = $input['timestamp'];
            $args['count'] = $input['count'];
            if (isset($input['lastVisitTimestamp'])) {
                $args['lastVisitTimestamp'] = $input['lastVisitTimestamp'];
            }
        }

        $args['sortByTimestamp'] = $input['sortByTimestamp'];

        /** @var array $visits */
        $visits = $this->iDataBaseRetrieveLaserVisits->{$method}(...$args);

        return response()->json($visits);
    }

    private function regularIndex(array $input): JsonResponse
    {
        $method = 'getVisits';
        if (isset($input['accountId'])) {
            $method .= 'ByUser';

            $targetUser = $this->iDataBaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername((int)$input['accountId']));
            $args['targetUser'] = $targetUser;
        } elseif (isset($input['orderId'])) {
            $method .= 'ByOrder';

            $order = $this->iDataBaseRetrieveRegularOrders->getRegularOrderById((int)$input['orderId']);
            $args['regularOrder'] = $order;
        } elseif (isset($input['timestamp']) && isset($input['operator'])) {
            $method .= 'ByTimestamp';
            $args['roleName'] = $input['roleName'];
            $args['operator'] = $input['operator'];
            $args['timestamp'] = $input['timestamp'];
            $args['count'] = $input['count'];
            if (isset($input['lastVisitTimestamp'])) {
                $args['lastVisitTimestamp'] = $input['lastVisitTimestamp'];
            }
        }

        $args['sortByTimestamp'] = $input['sortByTimestamp'];

        /** @var array $visits */
        $visits = $this->iDataBaseRetrieveRegularVisits->{$method}(...$args);

        return response()->json($visits);
    }

    /**
     * hasn't been tested yet
     */
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
        $validatedInput = $request->all();

        if (isset($validatedInput['weeklyTimePatterns'])) {
            $userInput = DSWeeklyTimePatterns::toObject($validatedInput['weeklyTimePatterns']);
        } elseif (isset($validatedInput['dateTimePeriods'])) {
            $userInput = DSDateTimePeriods::toObject($validatedInput['dateTimePeriods']);
        }

        $laserOrder = $this->iDataBaseRetrieveLaserOrders->getLaserOrderById($validatedInput['laserOrderId']);

        $iFindVisit = $this->visitsManagement->getLaserVisitFinder($laserOrder, isset($userInput) ? $userInput : null);

        try {
            $laserVisit = $this->iDataBaseCreateLaserVisit->createLaserVisit($laserOrder, $iFindVisit);
        } catch (VisitTimeSearchFailure $th) {
            return response()->json(['message' => trans_choice('Visits/visits.weeklySearchFailure', 0)], 422);
        } catch (NeededTimeOutOfRange $th) {
            return response()->json(['message' => trans_choice('Visits/visits.neededTimeOutOfRange', 0)], 422);
        }

        return response()->json($laserVisit->toArray());
    }

    public function regularStore(RegularStoreRequest $request): JsonResponse
    {
        $validatedInput = $request->all();

        if (isset($validatedInput['weeklyTimePatterns'])) {
            $userInput = DSWeeklyTimePatterns::toObject($validatedInput['weeklyTimePatterns']);
        } elseif (isset($validatedInput['dateTimePeriods'])) {
            $userInput = DSDateTimePeriods::toObject($validatedInput['dateTimePeriods']);
        }

        $regularOrder = $this->iDataBaseRetrieveRegularOrders->getRegularOrderById($validatedInput['regularOrderId']);

        $iFindVisit = $this->visitsManagement->getRegularVisitFinder($regularOrder, isset($userInput) ? $userInput : null);

        try {
            $regularVisit = $this->iDataBaseCreateRegularVisit->createRegularVisit($regularOrder, $iFindVisit);
        } catch (VisitTimeSearchFailure $th) {
            return response()->json(['message' => trans_choice('Visits/visits.weeklySearchFailure', 0)], 422);
        } catch (NeededTimeOutOfRange $th) {
            return response()->json(['message' => trans_choice('Visits/visits.neededTimeOutOfRange', 0)], 422);
        }

        return response()->json($regularVisit->toArray());
    }

    public function laserShowAvailable(LaserShowAvailableRequest $request): JsonResponse|RedirectResponse
    {
        $validatedInput = $request->all();

        if (isset($validatedInput['weeklyTimePatterns'])) {
            $userInput = DSWeeklyTimePatterns::toObject($validatedInput['weeklyTimePatterns']);
        } elseif (isset($validatedInput['dateTimePeriods'])) {
            $userInput = DSDateTimePeriods::toObject($validatedInput['dateTimePeriods']);
        }

        try {
            $timestamp = $this->visitsManagement->getLaserVisitFinder($validatedInput['laserOrderId'], isset($userInput) ? $userInput : null)->findVisit();
        } catch (VisitTimeSearchFailure $th) {
            return response()->json(['message' => trans_choice('Visits/visits.weeklySearchFailure', 0)], 422);
        } catch (NeededTimeOutOfRange $th) {
            return response()->json(['message' => trans_choice('Visits/visits.neededTimeOutOfRange', 0)], 422);
        }

        return response()->json(['availableVisitTimestamp' => $timestamp]);
    }

    public function regularShowAvailable(RegularShowAvailableRequest $request): JsonResponse|RedirectResponse
    {
        $validatedInput = $request->all();

        if (isset($validatedInput['weeklyTimePatterns'])) {
            $userInput = DSWeeklyTimePatterns::toObject($validatedInput['weeklyTimePatterns']);
        } elseif (isset($validatedInput['dateTimePeriods'])) {
            $userInput = DSDateTimePeriods::toObject($validatedInput['dateTimePeriods']);
        }

        try {
            $timestamp = $this->visitsManagement->getRegularVisitFinder($validatedInput['regularOrderId'], isset($userInput) ? $userInput : null)->findVisit();
        } catch (VisitTimeSearchFailure $th) {
            return response()->json(['message' => trans_choice('Visits/visits.weeklySearchFailure', 0)], 422);
        } catch (NeededTimeOutOfRange $th) {
            return response()->json(['message' => trans_choice('Visits/visits.neededTimeOutOfRange', 0)], 422);
        }

        return response()->json(['availableVisitTimestamp' => $timestamp]);
    }

    public function laserDestroy(LaserDestroyRequest $request, int $visitId): Response
    {
        $laserVisit = $this->iDataBaseRetrieveLaserVisits->getLaserVisitById($visitId);

        $this->iDataBaseDeleteLaserVisit->deleteLaserVisit($laserVisit);

        return response(trans_choice('Visits/visits.destroy', 0), 200);
    }

    public function regularDestroy(RegularDestroyRequest $request, int $visitId): Response
    {
        $regularVisit = $this->iDataBaseRetrieveRegularVisits->getRegularVisitById($visitId);

        $this->iDataBaseDeleteRegularVisit->deleteRegularVisit($regularVisit);

        return response(trans_choice('Visits/visits.destroy', 0), 200);
    }
}
