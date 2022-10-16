<?php

namespace App\Http\Controllers\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CalculatePartsAndPackagesRquest;
use App\Http\Requests\Orders\IndexRequest;
use App\Http\Requests\Orders\StoreRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\RegularOrder;
use App\Models\User;
use Database\Interactions\Orders\Creation\DatabaseCreateDefaultRegularOrder;
use Database\Interactions\Orders\Creation\DatabaseCreateLaserOrder;
use Database\Interactions\Orders\Creation\DatabaseCreateRegularOrder;
use Database\Interactions\Orders\Deletion\DataBaseDeleteLaserOrder;
use Database\Interactions\Orders\Deletion\DataBaseDeleteRegularOrder;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveLaserOrders;
use Database\Interactions\Orders\Retrieval\DatabaseRetrieveRegularOrders;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\PoliciesLogic\Order\Laser\Calculations\PriceCalculator;
use App\PoliciesLogic\Order\Laser\Calculations\TimeConsumptionCalculator;
use App\PoliciesLogic\Order\Laser\LaserOrder as LaserLaserOrder;
use App\Http\Requests\Orders\DestroyRequest;
use App\Http\Requests\Orders\OrdersCountRequest;
use App\PoliciesLogic\Order\ICalculateLaserOrder;
use App\PoliciesLogic\Order\Laser\ILaserPriceCalculator;
use App\PoliciesLogic\Order\Laser\ILaserTimeConsumptionCalculator;
use App\UseCases\Accounts\AccountsManagement;
use App\UseCases\Accounts\Interfaces\IDataBaseRetrieveAccounts;
use App\UseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteLaserOrder;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Database\Interactions\Accounts\DataBaseRetrieveAccounts;
use Illuminate\Database\Eloquent\Builder;

class OrdersController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    private AccountsManagement $accountsManagement;

    private IDataBaseRetrieveAccounts $iDataBaseRetrieveAccounts;

    private IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder;

    private IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder;

    private IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder;

    private IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders;

    private IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders;

    private IDataBaseDeleteLaserOrder $iDataBaseDeleteLaserOrder;

    private IDataBaseDeleteRegularOrder $iDataBaseDeleteRegularOrder;

    private ILaserPriceCalculator $iLaserPriceCalculator;

    private ILaserTimeConsumptionCalculator $iLaserTimeConsumptionCalculator;

    private ICalculateLaserOrder $iCalculateLaserOrder;

    public function __construct(
        null|CheckAuthentication $checkAuthentication = null,
        null|AccountsManagement $accountsManagement = null,
        null|IDataBaseRetrieveAccounts $iDataBaseRetrieveAccounts = null,
        null|IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder = null,
        null|IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder = null,
        null|ILaserPriceCalculator $iLaserPriceCalculator = null,
        null|ILaserTimeConsumptionCalculator $iLaserTimeConsumptionCalculator = null,
        null|ICalculateLaserOrder $iCalculateLaserOrder = null,
        null|IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders = null,
        null|IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders = null,
        null|IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder = null,
        null|IDataBaseDeleteLaserOrder $iDataBaseDeleteLaserOrder = null,
        null|IDataBaseDeleteRegularOrder $iDataBaseDeleteRegularOrder = null
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;
        $this->accountsManagement = $accountsManagement ?: new AccountsManagement;
        $this->iDataBaseRetrieveAccounts = $iDataBaseRetrieveAccounts ?: new DataBaseRetrieveAccounts;

        $this->iDataBaseRetrieveLaserOrders = $iDataBaseRetrieveLaserOrders ?: new DatabaseRetrieveLaserOrders;
        $this->iDataBaseRetrieveRegularOrders = $iDataBaseRetrieveRegularOrders ?: new DatabaseRetrieveRegularOrders;

        $this->iDataBaseCreateLaserOrder = $iDataBaseCreateLaserOrder ?: new DataBaseCreateLaserOrder;
        $this->iDataBaseCreateRegularOrder = $iDataBaseCreateRegularOrder ?: new DatabaseCreateRegularOrder;
        $this->iDataBaseCreateDefaultRegularOrder = $iDataBaseCreateDefaultRegularOrder ?: new DatabaseCreateDefaultRegularOrder;

        $this->iLaserPriceCalculator = $iLaserPriceCalculator ?: new PriceCalculator;
        $this->iLaserTimeConsumptionCalculator = $iLaserTimeConsumptionCalculator ?: new TimeConsumptionCalculator;
        $this->iCalculateLaserOrder = $iCalculateLaserOrder ?: new LaserLaserOrder();

        $this->iDataBaseDeleteLaserOrder = $iDataBaseDeleteLaserOrder ?: new DataBaseDeleteLaserOrder;
        $this->iDataBaseDeleteRegularOrder = $iDataBaseDeleteRegularOrder ?: new DataBaseDeleteRegularOrder;
    }

    public function laserIndex(IndexRequest $request): JsonResponse
    {
        $input = $request->safe()->all();

        $args = [];

        if (!isset($input['username'])) {
            $args['roleName'] = $input['roleName'];

            if (isset($input['lastOrderId'])) {
                $args['lastOrderId'] = $input['lastOrderId'];
            }

            // $args['lastOrderId'] = isset($input['lastOrderId']) ? $input['lastOrderId'] : null;
            $args['count'] = $input['count'];
        } else {
            $args['targetUser'] = $this->iDataBaseRetrieveAccounts->getAccount($input['username']);
        }

        if (isset($input['priceOtherwiseTime'])) {
            $input['priceOtherwiseTime'] = boolval($input['priceOtherwiseTime']);

            $args['operator'] = $input['operator'];

            if ($input['priceOtherwiseTime']) {
                $args['price'] = $input['price'];
            } else {
                $args['timeConsumption'] = $input['timeConsumption'];
            }
        }

        $method = 'getLaserOrders' .
            (isset($input['priceOtherwiseTime']) && $input['priceOtherwiseTime'] === true
                ? 'ByPrice'
                : (isset($input['priceOtherwiseTime']) && $input['priceOtherwiseTime'] === false
                    ? 'ByTimeConsumption'
                    : ''
                )
            ) .
            (!isset($input['username']) ? '' : 'ByUser')
            //
        ;

        /** @var array $orders */
        $orders = $this->iDataBaseRetrieveLaserOrders->{$method}(...$args);

        return response()->json($orders);
    }

    public function regularIndex(IndexRequest $request): JsonResponse
    {
        $input = $request->safe()->all();

        $args = [];

        if (!isset($input['username'])) {
            $args['roleName'] = $input['roleName'];

            if (isset($input['lastOrderId'])) {
                $args['lastOrderId'] = $input['lastOrderId'];
            }

            // $args['lastOrderId'] = isset($input['lastOrderId']) ? $input['lastOrderId'] : null;
            $args['count'] = $input['count'];
        } else {
            $args['targetUser'] = $this->iDataBaseRetrieveAccounts->getAccount($input['username']);
        }

        if (isset($input['priceOtherwiseTime'])) {
            $input['priceOtherwiseTime'] = boolval($input['priceOtherwiseTime']);

            $args['operator'] = $input['operator'];

            if ($input['priceOtherwiseTime']) {
                $args['price'] = $input['price'];
            } else {
                $args['timeConsumption'] = $input['timeConsumption'];
            }
        }

        $method = 'getRegularOrders' .
            (isset($input['priceOtherwiseTime']) && $input['priceOtherwiseTime'] === true
                ? 'ByPrice'
                : (isset($input['priceOtherwiseTime']) && $input['priceOtherwiseTime'] === false
                    ? 'ByTimeConsumption'
                    : ''
                )
            ) .
            (!isset($input['username']) ? '' : 'ByUser');

        /** @var array $orders */
        $orders = $this->iDataBaseRetrieveRegularOrders->{$method}(...$args);

        return response()->json($orders);
    }

    public function store(StoreRequest $request): JsonResponse|Response
    {
        $validatedInput = $request->safe()->all();
        $authenticated = $this->checkAuthentication->getAuthenticated();

        if (!isset($validatedInput['accountId'])) {
            $user = $authenticated;
        } else {
            $user = $this->iDataBaseRetrieveAccounts->getAccount($this->accountsManagement->resolveUsername($validatedInput['accountId']));
        }
        $userRoleName = $user->authenticatableRole->role->roleName->name;

        $isSelf = $user->getKey() === $authenticated->getKey();
        $gender = $user->gender;

        $businessName = strtolower($validatedInput['businessName']);
        switch ($businessName) {
            case 'laser':
                $parts = $this->iDataBaseRetrieveLaserOrders->collectDSPartsFromNames(!isset($validatedInput['parts']) ? [] : $validatedInput['parts'], $gender);
                $packages = $this->iDataBaseRetrieveLaserOrders->collectDSPacakgesFromNames(!isset($validatedInput['packages']) ? [] : $validatedInput['packages'], $gender);

                if (($parts !== null && $user->gender !== $parts->getGender()) || ($packages !== null && $user->gender !== $packages->getGender())) {
                    return response('User, parts and packages must have the same gender.', 422);
                } elseif ($parts === null && $packages === null) {
                    return response('One of the parts or packages must exist.', 422);
                }

                $order = $this->iDataBaseCreateLaserOrder->createLaserOrder(
                    $user,
                    $this->iCalculateLaserOrder->calculatePrice($parts, $packages, $this->iLaserPriceCalculator),
                    $this->iCalculateLaserOrder->calculateTimeConsumption($parts, $packages, $this->iLaserTimeConsumptionCalculator),
                    $this->iCalculateLaserOrder->calculatePriceWithoutDiscount($parts, $packages, $this->iLaserPriceCalculator),
                    $parts,
                    $packages
                );
                break;

            case 'regular':
                $editRegularOrderPrice = $editRegularOrderNeededTime = false;
                foreach ($user->authenticatableRole->role->role->privilegesSubjects as $privilege) {
                    $privilegeName = $privilege->privilegeName->name;

                    if (!in_array($privilegeName, ['editRegularOrderPrice', 'editRegularOrderNeededTime'])) {
                        continue;
                    }

                    if (($isSelf && $privilege->object !== null) || (!$isSelf && ($privilege->object === null || ($privilege->object !== null && $privilege->relatedObject->childRoleModel->roleName->name !== $userRoleName)))) {
                        continue;
                    }

                    if ($privilegeName === 'editRegularOrderPrice') {
                        $editRegularOrderPrice = true;
                    } elseif ($privilegeName === 'editRegularOrderNeededTime') {
                        $editRegularOrderNeededTime = true;
                    }

                    if ($editRegularOrderPrice && $editRegularOrderNeededTime) {
                        break;
                    }
                }

                if ($editRegularOrderPrice && $editRegularOrderNeededTime && isset($validatedInput['price'])  && isset($validatedInput['timeConsumption'])) {
                    $order = $this->iDataBaseCreateRegularOrder->createRegularOrder($user, $validatedInput['price'], $validatedInput['timeConsumption']);
                } elseif (isset($validatedInput['price'])  || isset($validatedInput['timeConsumption'])) {
                    return response(trans_choice('auth.User-Not-Authorized', 0), 403);
                } else {
                    $order = $this->iDataBaseCreateDefaultRegularOrder->createDefaultRegularOrder($user);
                }
                break;

            default:
                return response('There\'s no such business name: ' . strval($validatedInput['businessName']), 404);
                break;
        }

        return response()->json($order->toArray());
    }

    public function ordersCount(OrdersCountRequest $request): Response
    {
        $input = $request->safe()->all();
        $roleName = $input['roleName'];
        $user = (new CheckAuthentication)->getAuthenticated();
        $userRoleName = $user->authenticatableRole->role->roleName->name;
        $canReadSelf = false;
        foreach ($user->authenticatableRole->role->role->retrieveOrderSubjects as $retrieveOrder) {
            if ($retrieveOrder->object !== null) {
                continue;
            }
            $canReadSelf = true;
            break;
        }
        $isSelf = $userRoleName === $roleName;

        /** @var Builder $query */
        switch ($input['businessName']) {
            case 'laser':
                $query = LaserOrder::query();
                break;

            case 'regular':
                $query = RegularOrder::query();
                break;

            default:
                break;
        }

        $count = $query
            ->whereHas('order', function (Builder $query) use ($input, $user, $isSelf, $canReadSelf) {
                $query->whereHas('user', function (Builder $query) use ($input, $user, $isSelf, $canReadSelf) {
                    if ($isSelf && !$canReadSelf) {
                        $query->whereKeyNot($user->getKey());
                    }
                    $i = 0;
                    foreach ((new User)->getChildrenTypesRelationNames() as $relation) {
                        if ($i === 0) {
                            $query->whereHas($relation, function (Builder $query) use ($input) {
                                $query->whereHas('role', function (Builder $query) use ($input) {
                                    $query->whereHas('roleName', function (Builder $query) use ($input) {
                                        $query->where('name', '=', $input['roleName']);
                                    });
                                });
                            });
                        } else {
                            $query->orWhereHas($relation, function (Builder $query) use ($input) {
                                $query->whereHas('role', function (Builder $query) use ($input) {
                                    $query->whereHas('roleName', function (Builder $query) use ($input) {
                                        $query->where('name', '=', $input['roleName']);
                                    });
                                });
                            });
                        }
                        $i++;
                    }
                });
            })
            ->count()
            //
        ;
        return response($count);
    }

    public function destroy(DestroyRequest $request): Response|ResponseFactory
    {
        $validatedInput = $request->safe()->all();

        switch ($validatedInput['businessName']) {
            case 'laser':
                $order = $this->iDataBaseRetrieveLaserOrders->getLaserOrderById($validatedInput['childOrderId']);
                $this->iDataBaseDeleteLaserOrder->deleteLaserOrder($order);
                break;

            case 'regular':
                $order = $this->iDataBaseRetrieveRegularOrders->getRegularOrderById($validatedInput['childOrderId']);
                $this->iDataBaseDeleteRegularOrder->deleteRegularOrder($order);
                break;

            default:
                return response('The provided business name doesn\'t exist.', 404);
                break;
        }

        return response(trans_choice('Orders/destroy.successfull', 0), 200);
    }

    public function calculateTime(CalculatePartsAndPackagesRquest $request): Response
    {
        $validatedInput = $request->safe()->all();
        $user = $this->checkAuthentication->getAuthenticated();
        $gender = isset($validatedInput['gender']) ? $validatedInput['gender'] : $user->gender;

        return response($this->iCalculateLaserOrder->calculateTimeConsumption(
            $this->iDataBaseRetrieveLaserOrders->collectDSPartsFromNames(isset($validatedInput['parts']) ? $validatedInput['parts'] : [], $gender),
            $this->iDataBaseRetrieveLaserOrders->collectDSPacakgesFromNames(isset($validatedInput['packages']) ? $validatedInput['packages'] : [], $gender),
            $this->iLaserTimeConsumptionCalculator
        ));
    }

    public function calculatePrice(CalculatePartsAndPackagesRquest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $user = $this->checkAuthentication->getAuthenticated();
        $gender = isset($validatedInput['gender']) ? $validatedInput['gender'] : $user->gender;

        return response()->json([
            'price' => $this->iCalculateLaserOrder->calculatePrice(
                $this->iDataBaseRetrieveLaserOrders->collectDSPartsFromNames(isset($validatedInput['parts']) ? $validatedInput['parts'] : [], $gender),
                $this->iDataBaseRetrieveLaserOrders->collectDSPacakgesFromNames(isset($validatedInput['packages']) ? $validatedInput['packages'] : [], $gender),
                $this->iLaserPriceCalculator
            ),
            'priceWithoutDiscount' => $this->iCalculateLaserOrder->calculatePriceWithoutDiscount(
                $this->iDataBaseRetrieveLaserOrders->collectDSPartsFromNames(isset($validatedInput['parts']) ? $validatedInput['parts'] : [], $gender),
                $this->iDataBaseRetrieveLaserOrders->collectDSPacakgesFromNames(isset($validatedInput['packages']) ? $validatedInput['packages'] : [], $gender),
                $this->iLaserPriceCalculator
            )
        ]);
    }
}
