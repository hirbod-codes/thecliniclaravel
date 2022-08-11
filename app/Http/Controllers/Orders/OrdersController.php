<?php

namespace App\Http\Controllers\Orders;

use App\Auth\CheckAuthentication;
use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CalculatePartsAndPackagesRquest;
use App\Http\Requests\Orders\IndexRequest;
use App\Http\Requests\Orders\StoreRequest;
use App\Models\Order\LaserOrder;
use App\Models\Order\Order;
use App\Models\Order\RegularOrder;
use App\Models\Package\Package;
use App\Models\Part\Part;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\PoliciesLogic\Order\Laser\Calculations\PriceCalculator;
use App\PoliciesLogic\Order\Laser\Calculations\TimeConsumptionCalculator;
use App\PoliciesLogic\Order\Laser\LaserOrder as LaserLaserOrder;
use App\DataStructures\Order\DSPackages;
use App\DataStructures\Order\DSParts;
use App\Http\Requests\Orders\DestroyRequest;
use App\Http\Requests\Orders\OrdersCountRequest;
use App\UseCases\Orders\Creation\LaserOrderCreation;
use App\UseCases\Orders\Creation\RegularOrderCreation;
use App\UseCases\Orders\Deletion\LaserOrderDeletion;
use App\UseCases\Orders\Deletion\RegularOrderDeletion;
use App\UseCases\Orders\Interfaces\IDataBaseCreateLaserOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseCreateDefaultRegularOrder;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteLaserOrder;
use App\UseCases\Orders\Interfaces\IDataBaseDeleteRegularOrder;
use App\UseCases\Orders\Retrieval\LaserOrderRetrieval;
use App\UseCases\Orders\Retrieval\RegularOrderRetrieval;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveLaserOrders;
use App\UseCases\Orders\Interfaces\IDataBaseRetrieveRegularOrders;
use Illuminate\Database\Eloquent\Builder;

class OrdersController extends Controller
{
    private CheckAuthentication $checkAuthentication;

    private RegularOrderCreation $regularOrderCreation;

    private LaserOrderCreation $laserOrderCreation;

    private IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder;

    private IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder;

    private IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder;

    private RegularOrderRetrieval $regularOrderRetrieval;

    private LaserOrderRetrieval $laserOrderRetrieval;

    private IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders;

    private IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders;

    private RegularOrderDeletion $regularOrderDeletion;

    private LaserOrderDeletion $laserOrderDeletion;

    private IDataBaseDeleteLaserOrder $iDataBaseDeleteLaserOrder;

    private IDataBaseDeleteRegularOrder $iDataBaseDeleteRegularOrder;

    public function __construct(
        null|CheckAuthentication $checkAuthentication = null,
        null|RegularOrderRetrieval $regularOrderRetrieval = null,
        null|LaserOrderRetrieval $laserOrderRetrieval = null,
        null|RegularOrderCreation $regularOrderCreation = null,
        null|LaserOrderCreation $laserOrderCreation = null,
        null|IDataBaseCreateRegularOrder $iDataBaseCreateRegularOrder = null,
        null|IDataBaseCreateLaserOrder $iDataBaseCreateLaserOrder = null,
        null|IDataBaseRetrieveLaserOrders $iDataBaseRetrieveLaserOrders = null,
        null|IDataBaseRetrieveRegularOrders $iDataBaseRetrieveRegularOrders = null,
        null|IDataBaseCreateDefaultRegularOrder $iDataBaseCreateDefaultRegularOrder = null,
        null|RegularOrderDeletion $regularOrderDeletion = null,
        null|LaserOrderDeletion $laserOrderDeletion = null,
        null|IDataBaseDeleteLaserOrder $iDataBaseDeleteLaserOrder = null,
        null|IDataBaseDeleteRegularOrder $iDataBaseDeleteRegularOrder = null
    ) {
        $this->checkAuthentication = $checkAuthentication ?: new CheckAuthentication;

        $this->regularOrderRetrieval = $regularOrderRetrieval ?: new RegularOrderRetrieval;
        $this->laserOrderRetrieval = $laserOrderRetrieval ?: new LaserOrderRetrieval;
        $this->iDataBaseRetrieveLaserOrders = $iDataBaseRetrieveLaserOrders ?: new DatabaseRetrieveLaserOrders;
        $this->iDataBaseRetrieveRegularOrders = $iDataBaseRetrieveRegularOrders ?: new DatabaseRetrieveRegularOrders;

        $this->regularOrderCreation = $regularOrderCreation ?: new RegularOrderCreation;
        $this->laserOrderCreation = $laserOrderCreation ?: new LaserOrderCreation;
        $this->iDataBaseCreateLaserOrder = $iDataBaseCreateLaserOrder ?: new DataBaseCreateLaserOrder;
        $this->iDataBaseCreateRegularOrder = $iDataBaseCreateRegularOrder ?: new DatabaseCreateRegularOrder;
        $this->iDataBaseCreateDefaultRegularOrder = $iDataBaseCreateDefaultRegularOrder ?: new DatabaseCreateDefaultRegularOrder;

        $this->regularOrderDeletion = $regularOrderDeletion ?: new RegularOrderDeletion;
        $this->laserOrderDeletion = $laserOrderDeletion ?: new LaserOrderDeletion;
        $this->iDataBaseDeleteLaserOrder = $iDataBaseDeleteLaserOrder ?: new DataBaseDeleteLaserOrder;
        $this->iDataBaseDeleteRegularOrder = $iDataBaseDeleteRegularOrder ?: new DataBaseDeleteRegularOrder;
    }

    public function laserIndex(IndexRequest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $validatedInput['businessName'] = 'laser';
        return $this->index($validatedInput);
    }

    public function regularIndex(IndexRequest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $validatedInput['businessName'] = 'regular';
        return $this->index($validatedInput);
    }

    private function index(array $input): JsonResponse
    {
        $args = [];
        $args['db'] = $this->{'iDataBaseRetrieve' . ucfirst($input['businessName']) . 'Orders'};

        if (!isset($input['username'])) {
            $args['roleName'] = $input['roleName'];

            if (isset($input['lastOrderId'])) {
                $args['lastOrderId'] = $input['lastOrderId'];
            }

            // $args['lastOrderId'] = isset($input['lastOrderId']) ? $input['lastOrderId'] : null;
            $args['count'] = $input['count'];
        } else {
            $args['targetUser'] = User::query()->where('username', $input['username'])->firstOrFail();
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

        $method = 'get' . ucfirst($input['businessName']) . 'Orders' .
            (isset($input['priceOtherwiseTime']) && $input['priceOtherwiseTime'] === true
                ? 'ByPrice'
                : (isset($input['priceOtherwiseTime']) && $input['priceOtherwiseTime'] === false
                    ? 'ByTimeConsumption'
                    : ''
                )
            ) .
            (!isset($input['username']) ? '' : 'ByUser');

        /** @var array $orders */
        $orders = $this->{strtolower($input['businessName']) . 'OrderRetrieval'}->{$method}(...$args);

        return response()->json($orders);
    }

    public function store(StoreRequest $request): RedirectResponse|JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $authenticated = $this->checkAuthentication->getAuthenticated();

        if (!isset($validatedInput['accountId'])) {
            $user = $authenticated;
        } else {
            /** @var User $user */
            $user = User::query()->whereKey($validatedInput['accountId'])->firstOrFail();
        }
        $userRoleName = $user->authenticatableRole->role->roleName->name;

        $isSelf = $user->getKey() === $authenticated->getKey();
        $gender = $user->gender;

        $businessName = strtolower($validatedInput['businessName']);
        switch ($businessName) {
            case 'laser':
                $parts = $this->collectDSPartsFromNames(!isset($validatedInput['parts']) ? [] : $validatedInput['parts'], $gender);

                $packages = $this->collectDSPacakgesFromNames(!isset($validatedInput['packages']) ? [] : $validatedInput['packages'], $gender);

                $order = $this->laserOrderCreation->createLaserOrder($user, $this->iDataBaseCreateLaserOrder, $parts, $packages);
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
                }

                if ($editRegularOrderPrice && $editRegularOrderNeededTime && isset($validatedInput['price'])  && isset($validatedInput['timeConsumption'])) {
                    $order = $this->regularOrderCreation->createRegularOrder($validatedInput['price'], $validatedInput['timeConsumption'], $user, $this->iDataBaseCreateRegularOrder);
                } elseif (isset($validatedInput['price'])  || isset($validatedInput['timeConsumption'])) {
                    throw new \RuntimeException(trans_choice('auth.User-Not-Authorized', 0), 403);
                } else {
                    $order = $this->regularOrderCreation->createDefaultRegularOrder($user, $this->iDataBaseCreateDefaultRegularOrder);
                }
                break;

            default:
                throw new \LogicException('There\'s no such business name: ' . strval($validatedInput['businessName']), 500);
                break;
        }

        return response()->json($order->toArray());
    }

    private function collectDSPacakgesFromNames(array $packagesNames = [], string $gender): DSPackages
    {
        if (count($packagesNames) === 0) {
            return new DSPackages($gender);
        }

        $packages = Package::query();
        foreach ($requestPackages = $packagesNames as $packageName) {
            $packages = $packages->where('name', '=', $packageName, count($packagesNames) !== 1 ? 'or' : 'and');
        }
        $packages = $packages->get()->all();

        return Package::getDSPackages($packages, $gender);
    }

    private function collectDSPartsFromNames(array $partsNames = [], string $gender): DSParts
    {
        if (count($partsNames) === 0) {
            return new DSParts($gender);
        }

        $parts = Part::query();
        foreach ($requestParts = $partsNames as $partName) {
            $parts = $parts->where('name', '=', $partName, count($partsNames) !== 1 ? 'or' : 'and');
        }
        $parts = $parts->get()->all();

        return Part::getDSParts($parts, $gender);
    }

    public function ordersCount(OrdersCountRequest $request): Response
    {
        $input = $request->safe()->all();
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
            ->whereHas('order', function (Builder $query) use ($input) {
                $query->whereHas('user', function (Builder $query) use ($input) {
                    foreach ((new User)->getChildrenTypesRelationNames() as $relation) {
                        $query->orWhereHas($relation, function (Builder $query) use ($input) {
                            $query->whereHas('role', function (Builder $query) use ($input) {
                                $query->whereHas('roleName', function (Builder $query) use ($input) {
                                    $query->where('name', '=', $input['roleName']);
                                });
                            });
                        });
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
                $order = LaserOrder::query()->whereKey($validatedInput['childOrderId'])->firstOrFail();
                break;

            case 'regular':
                $order = RegularOrder::query()->whereKey($validatedInput['childOrderId'])->firstOrFail();
                break;

            default:
                throw new \LogicException('!!!', 500);
                break;
        }

        $this->{$validatedInput['businessName'] . 'OrderDeletion'}->{'delete' . ucfirst($validatedInput['businessName']) . 'Order'}($order, $this->{'iDataBaseDelete' . ucfirst($validatedInput['businessName']) . 'Order'});

        return response(trans_choice('Orders/destroy.successfull', 200));
    }

    public function calculateTime(CalculatePartsAndPackagesRquest $request): Response
    {
        $validatedInput = $request->safe()->all();
        $user = $this->checkAuthentication->getAuthenticated();
        $gender = isset($validatedInput['gender']) ? $validatedInput['gender'] : $user->gender;

        return response((new LaserLaserOrder)->calculateTimeConsumption(
            $this->collectDSPartsFromNames(isset($validatedInput['parts']) ? $validatedInput['parts'] : [], $gender),
            $this->collectDSPacakgesFromNames(isset($validatedInput['packages']) ? $validatedInput['packages'] : [], $gender),
            (new TimeConsumptionCalculator)
        ));
    }

    public function calculatePrice(CalculatePartsAndPackagesRquest $request): JsonResponse
    {
        $validatedInput = $request->safe()->all();
        $user = $this->checkAuthentication->getAuthenticated();
        $gender = isset($validatedInput['gender']) ? $validatedInput['gender'] : $user->gender;

        return response()->json([
            'price' => (new LaserLaserOrder)->calculatePrice(
                $this->collectDSPartsFromNames(isset($validatedInput['parts']) ? $validatedInput['parts'] : [], $gender),
                $this->collectDSPacakgesFromNames(isset($validatedInput['packages']) ? $validatedInput['packages'] : [], $gender),
                (new PriceCalculator)
            ),
            'priceWithoutDiscount' => (new LaserLaserOrder)->calculatePriceWithoutDiscount(
                $this->collectDSPartsFromNames(isset($validatedInput['parts']) ? $validatedInput['parts'] : [], $gender),
                $this->collectDSPacakgesFromNames(isset($validatedInput['packages']) ? $validatedInput['packages'] : [], $gender),
                (new PriceCalculator)
            )
        ]);
    }
}
